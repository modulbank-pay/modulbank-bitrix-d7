<?php

namespace Sale\Handlers\PaySystem;

require_once(dirname(__FILE__) . "/lib/fpayments.php");

use Bitrix\Main\Error;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Request;
use Bitrix\Main\Type\DateTime;
use Bitrix\Sale\Order;
use Bitrix\Sale\PaySystem;
use Bitrix\Sale\Payment;
use CCatalogVat;
use CModule;
use CSaleBasket;

use FPayments\PaymentForm;
use FPayments\FormError;
use FPayments\ReceiptItem;

Loc::loadMessages(__FILE__);


class modulbankHandler extends PaySystem\ServiceHandler implements PaySystem\ICheckable
{
    public function initiatePay(Payment $payment, Request $request = null)
    {
        $params = array('URL' => $this->getUrl($payment, 'pay'));
        $this->setExtraParams($params);

        try {
            $form = $this->getPaymentRequestData($payment);

            $this->setExtraParams([
                'HIDDEN_FIELDS' => PaymentForm::array_to_hidden_fields($form),
                'URL' => $this->getFpaymentsForm($payment)->get_url(),
            ]);

        } catch (FormError $e) {
            $this->setExtraParams([
                'ERROR' => $e->getMessage(),
            ]);
        }

        return $this->showTemplate($payment, 'template');
    }

    function getFpaymentsForm($payment)
    {
        return new PaymentForm(
            $this->getBusinessValue($payment, 'MERCHANT_ID'),
            $this->getBusinessValue($payment, 'SECRET_KEY'),
            $this->getBusinessValue($payment, 'TEST_MODE') == 'Y'
        );
    }


    /**
     * @param Payment $payment
     * @return array
     * @throws FormError
     */
    protected function getPaymentRequestData(Payment $payment)
    {
        $form = $this->getFpaymentsForm($payment);
        $form->enable_callback_on_failure();

        $value = function ($field) use ($payment) {
            return $this->getBusinessValue($payment, $field);
        };

        $values = $form->compose(
            RoundEx($value('PAYMENT_SHOULD_PAY'), 2),
            $value('CURRENCY'),
            $value('PAYMENT_ID'),
            $value('CLIENT_EMAIL'),
            $value('CLIENT_FIRST_NAME') . ' ' . $value('CLIENT_LAST_NAME'),
            $value('CLIENT_PHONE'),
            $this->interpolateHostVars($value('SUCCESS_URL')) . '?payment_id=' . $value('PAYMENT_ID'),
            $this->interpolateHostVars($value('FAIL_URL')),
            $this->interpolateHostVars($value('CANCEL_URL')),
            $this->interpolateHostVars('{schema}://{host}/bitrix/tools/sale_ps_result.php') . '?payment_id=' . $value('PAYMENT_ID'),
            '',
            $this->interpolateOrderVars($value('ORDER_DESCRIPTION'), $payment),
            empty($value('CLIENT_EMAIL')) ? $value('CLIENT_PHONE') : $value('CLIENT_EMAIL'),
            $this->getReceiptItems($payment)
        );

        return $values;
    }

    private function interpolateHostVars($string)
    {
        $request = \Bitrix\Main\Application::getInstance()->getContext()->getRequest();

        $hostname = $request->getHttpHost();
        $schema = $request->isHttps() ? 'https' : 'http';

        return preg_replace(
            ['/{host}/i', '/\{schema\}/i'],
            [$hostname, $schema],
            $string
        );
    }

    private function interpolateOrderVars($string, $payment)
    {
        $order_id = $this->getBusinessValue($payment, 'PAYMENT_ID');

        return preg_replace('/{order_id}/i', $order_id, $string);
    }

    /**
     * Преобразует ID налога битрикса в название налога, которое используется в API
     */
    private function vatIdToName($vat_id) {
        $vatData = CCatalogVat::GetByID($vat_id)->Fetch();

        $vat_rate = floatval($vatData['RATE']);
        return ReceiptItem::guess_vat($vat_rate);
    }

    private function getReceiptItems($payment)
    {
        list($orderID, $_) = \Bitrix\Sale\PaySystem\Manager::getIdsByPayment(
            $this->getBusinessValue($payment, 'ORDER_ID')
        );
        CModule::IncludeModule("catalog");

        $order = Order::load($orderID);

        $productsList = CSaleBasket::GetList(array(), array("ORDER_ID" => $orderID), false, false, array());

        $items = [];

        foreach ($productsList->arResult as $product) {
            $vat_option = $this->vatIdToName($product['VAT_ID']);

            $items[] = new ReceiptItem(
                $product['NAME'],
                $product['PRICE'],
                $product['QUANTITY'],
                $vat_option
            );
        }

        $shipments = $order->getShipmentCollection();

        foreach ($shipments as $shipment) {
            if (! $shipment->getPrice())
                continue;

            $delivery = $shipment->getDelivery();

            $vat_option = $this->vatIdToName($delivery->getVatId());

            $items[] = new ReceiptItem(
                GetMessage('MODULBANK_DELIVERY_TXT'),
                RoundEx($order->getDeliveryPrice(), 2),
                1,
                null,
                $vat_option
            );
        }

        return $items;
    }


    public function getPaymentIdFromRequest(Request $request)
    {
        return $request->get('order_id');
    }

    public function getCurrencyList()
    {
        return ['RUB'];
    }

    public static function getIndicativeFields()
    {
        return array('unix_timestamp');
    }

    /**
     * Обрабатывает полученные от платежной системы данные о статусе транзакции.
     * Данные могут быть получены либо через колбек, либо после осуществления
     * HTTP-запроса на проверку статуса транзакции, сделанного на success-странице.
     *
     * @param $payment Объект платежа
     * @param $data Данные от платежной системы
     * @param bool $requestMadeByUs Флаг того, что запрос был сделан нами (в данных не будет подписи)
     * @return PaySystem\ServiceResult
     */
    public function processPaymentResponse($payment, $data, $requestMadeByUs = false)
    {
        $result = new PaySystem\ServiceResult();

        $setError = function ($message) use ($result) {
            $result->addError(new Error($message));
            $result->setData([
                'error' => $message
            ]);
            PaySystem\ErrorLog::add(array(
                'MESSAGE' => $message
            ));
            return $result;
        };

        $form = $this->getFpaymentsForm($payment);

        if (!$requestMadeByUs) {
            if (!$form->is_signature_correct($data)) {
                return $setError('Incorrect signature');
            }

            if ($data['merchant'] != $this->getBusinessValue($payment, 'MERCHANT_ID')) {
                return $setError('Incorrect merchant');
            }
        }

        if ($data['order_id'] != $this->getBusinessValue($payment, 'PAYMENT_ID')) {
            return $setError('Incorrect order ID');
        }

        if (RoundEx($data['amount'], 2) != RoundEx($payment->getSum(), 2)) {
            return $setError('Incorrect payment amount');
        }

        if ($data['currency'] != $this->getBusinessValue($payment, 'CURRENCY')) {
            return $setError('Incorrect currency');
        }

        if ($payment->isPaid()) {
            PaySystem\ErrorLog::add(array(
                'MESSAGE' => 'Order is already paid, doing nothing',
            ));
            return $result;
        }

        if ($data['state'] == 'COMPLETE') {
            $result->setOperationType(PaySystem\ServiceResult::MONEY_COMING);
        }

        $psData = array(
            'PS_STATUS' => $data['state'] == 'COMPLETE' ? 'Y' : 'N',
            'PS_STATUS_MESSAGE' => $data['message'],
            'PS_RESPONSE_DATE' => new DateTime(),
            'PS_SUM' => (double)$data['amount'],
            'PS_CURRENCY' => $data['currency'],
        );

        $result->setPsData($psData);
        $result->setData([
            'status' => 'ok',
        ]);
        return $result;
    }

    /**
     * Обработчик колбека от платежной системы.
     */

    public function processRequest(Payment $payment, Request $request)
    {
        $payment_id = intval($_GET['payment_id']);
        if (!$payment_id)
            die("No payment ID in GET arguments provided");

        list($orderId, $paymentId) = \Bitrix\Sale\PaySystem\Manager::getIdsByPayment(intval($_GET['payment_id']));
        if (! $orderId)
            die("No order found by payment_id=$payment_id");

        $order = Order::load($orderId);
        if (!$order)
            die("Order '$orderId' can't be loaded");

        $paymentCollection = $order->getPaymentCollection();
        if (!$paymentCollection)
            die("Can't load payment collection");

        $payment = $paymentCollection->getItemById($paymentId);
        if (!$payment)
            die("Can't find payment");

        return $this->processPaymentResponse($payment, $_POST);
    }

    public function check(Payment $payment)
    {
        $form = $this->getFpaymentsForm($payment);
        $data = (array)$form->get_transaction_info($_GET['transaction_id']);

        if (array_key_exists('transaction_id', $data)) {
            return $this->processPaymentResponse($payment, $data, true);
        }
        return false;
    }

    function sendResponse(PaySystem\ServiceResult $result, Request $request)
    {
        if ($result->isSuccess())
            die('OK ' . $request->get('order_id'));
        else
            die(implode("\n", $result->getErrorMessages()));

    }

    protected function isTestMode(Payment $payment = null)
    {
        return $this->getBusinessValue($payment, 'TEST_MODE') == 'Y';
    }
}
