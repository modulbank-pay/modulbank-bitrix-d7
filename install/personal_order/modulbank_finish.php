<?php

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");

use Bitrix\Sale\Order;

if ($_GET['payment_id'] && $_GET['transaction_id']) {
    \Bitrix\Main\Loader::includeModule("sale");
    \Bitrix\Main\Loader::includeModule("catalog");

    list($orderId, $paymentId) = \Bitrix\Sale\PaySystem\Manager::getIdsByPayment(intval($_GET['payment_id']));

    if ($orderId > 0) {
        $order = Order::load($orderId);

        if ($order) {
            $paymentCollection = $order->getPaymentCollection();

            if ($paymentCollection && $paymentId > 0) {

                $payment = $paymentCollection->getItemById($paymentId);

                if ($payment) {
                    $paySystem = \Bitrix\Sale\PaySystem\Manager::getObjectById($payment->getPaymentSystemId());
                    $paySystem->check($payment);
                }
            }
        }
    }
}

LocalRedirect("/personal/orders/");

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");
