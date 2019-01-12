<?php

namespace Sale\Handlers\PaySystem;

require_once($_SERVER['DOCUMENT_ROOT']."/bitrix/modules/modulbank.payments/lib/fpayments.php");

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
     * Returns the tax ID of the bitrix by product id
     */
	private function getVatIdByProductId($productId)
	{
		$vatId = 0;
		if (\Bitrix\Main\Loader::includeModule('catalog'))
		{
			$dbRes = \CCatalogProduct::GetVATInfo($productId);
			$vat = $dbRes->Fetch();
			if ($vat)
			{
				$vatId = (int)$vat['ID'];
			}
		}

		return $vatId;
	}
	
	/**
     * Returns the tax ID of the bitrix by value
     */
	private function getVatIdByVatRate($vatRate)
	{
		$vatId = 0;
		if (\Bitrix\Main\Loader::includeModule('catalog'))
		{
			$dbRes = Catalog\VatTable::getList(array('filter' => array('ACTIVE' => 'Y')));
			while ($data = $dbRes->fetch())
			{
				if((int)$data['RATE'] == $vatRate) {
					$vatId = (int)$data['ID'];
				}
			}
		}
		
		return $vatId;
	}
	
	/**
     * Returns the bitrix tax ID for the cart position
     */
	protected function getProductVatId($basketItem)
	{
		$vatId = $this->getVatIdByProductId($basketItem->getProductId());
		if ($vatId === 0)
		{
			$vatRate = (int)($basketItem->getVatRate() * 100);
			if ($vatRate > 0)
			{
				$vatId = $this->getVatIdByVatRate($vatRate);
			}
		}

		return (int)$vatId;
	}

    /**
     * Converts the bitrix tax ID to the tax name that is used in the API
     */
    private function vatIdToName($vat_id) {
        $vatData = CCatalogVat::GetByID($vat_id)->Fetch();

        $vat_rate = floatval($vatData['RATE']);
        return ReceiptItem::guess_vat($vat_rate);
    }
	
	private function distributionAmountOfProducts($amount, &$items) {
		if($amount > 0 && count($items) > 0) {
			$items_sum = 0;
			
			foreach($items as $item) {
				$items_sum += $item->get_sum();
			}
			
			$items_sum = round($items_sum, 2);
			
			if($items_sum > 0 && $items_sum != $amount) {
				$sumDiff = $items_sum - $amount;
				$max_price_item = null;
				$new_items_sum = 0;
				
				foreach($items as $item) {
					$percent = $item->get_sum() / $items_sum;
					$item_sum = $amount * $percent;
					$item_price = round($item_sum / $item->get_quantity(), 2);
					$item->set_price($item_price);
					
					$new_items_sum += $item_price * $item->get_quantity();
					if($max_price_item === null || ($max_price_item !== null && $item_price > $max_price_item->get_price())) {
						$max_price_item = $item;
					}
				}
				
				if($new_items_sum != $amount && $max_price_item !== null) {
					if($new_items_sum > $amount) {
						$item_price = round($max_price_item->get_price() + ($amount - $new_items_sum) / $max_price_item->get_quantity(), 2);
					}
					else {
						$item_price = round($max_price_item->get_price() - ($amount - $new_items_sum) / $max_price_item->get_quantity(), 2);
					}
					
					$max_price_item->set_price($item_price);
				}
			}
		}
	}
	
	private function getReceiptItems($payment)
    {
        list($orderID, $_) = \Bitrix\Sale\PaySystem\Manager::getIdsByPayment(
            $this->getBusinessValue($payment, 'ORDER_ID')
        );
        CModule::IncludeModule("catalog");

        $order = Order::load($orderID);

        $value = function ($field) use ($payment) {
            return $this->getBusinessValue($payment, $field);
        };
		
		$items = [];

        $shipments = $order->getShipmentCollection();

        foreach ($shipments as $shipment) {
			$shipmentItemCollection = $shipment->getShipmentItemCollection();
			$sellableItems = $shipmentItemCollection->getSellableItems();
			foreach($sellableItems as $shipmentItem) {
				$basketItem = $shipmentItem->getBasketItem();
				
				$vat_option = $this->vatIdToName($this->getProductVatId($basketItem));

				$items[] = new ReceiptItem(
					$basketItem->getField('NAME'),
					$basketItem->getPriceWithVat(),
					(float)$shipmentItem->getQuantity(),
					$vat_option,
					$value('SNO'),
					$value('PAYMENT_OBJECT'),
					$value('PAYMENT_METHOD')
				);
			}
			
            if($shipment->getPrice() > 0) {
				$vat_option = $this->vatIdToName($shipment->getDelivery()->getVatId());

				$items[] = new ReceiptItem(
					Loc::getMessage('MODULBANK_DELIVERY_TXT'),
					RoundEx($shipment->getPrice(), 2),
					1,
					$vat_option,
					$value('SNO'),
					'service',
					$value('PAYMENT_METHOD')
				);
			}
        }
		
		$this->distributionAmountOfProducts(RoundEx($value('PAYMENT_SHOULD_PAY'), 2), $items);
		
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
     * Processes transaction status data received from the payment system.
     * Data can be obtained either through a callback or after an
     * HTTP request is made to check the status of a transaction made on the success page.
     *
     * @param $payment Payment object
     * @param $data Data from the payment system
     * @param bool $requestMadeByUs Flag that the request was made by us (there will be no signature in the data)
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
     * Callback handler from the payment system.
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
