<?
use \Bitrix\Main\Application;
use \Bitrix\Sale\PaySystem;

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

if(CModule::IncludeModule("sale")) {
	$context = Application::getInstance()->getContext();
	$request = $context->getRequest();
	
	$orderId = $request->getQuery('order_id');
	$paymentId = $request->getQuery('payment_id');
	$action = $request->getQuery('action');
	$backURL = $request->getQuery('back_url');
	if(empty($backURL)) {
		$backURL = '/';
	}
	$result = false;
	
	if($orderId > 0 && $paymentId > 0 && check_bitrix_sessid()) {
		$order = \Bitrix\Sale\Order::load($orderId);
		
		if($order != false) {
			$paymentCollection = $order->getPaymentCollection();
			$payment = $paymentCollection->getItemById($paymentId);
			
			if($payment != false) {
				$paySystem = $payment->getPaySystem();
				
				if($action == 'confirm') {
					$result = $paySystem->confirm($payment);
				}
				elseif($action == 'cancel') {
					$result = $paySystem->cancel($payment);
				}
			}
		}
		
		if($result != false) {
			if($result->isSuccess()) {
				if($action == 'confirm') {
					?>
					Processing...
					<script>
						setTimeout(function(){
							location.href = '<?=$backURL?>';
						}, 10000);
					</script>
					<?
				}
				else {
					LocalRedirect($backURL);
				}
			}
			else {
				echo 'Error<br>';
				echo implode('<br>', $result->getErrors()).'<br>';
				echo '<a href="'.$backURL.'">Back to order</a>';
			}
		}
		else {
			LocalRedirect($backURL);
		}
	}
}
?>