<?
use Bitrix\Sale\Order;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class modulbankOrderEdit {
	function addHoldButtons($arOrderData) {
		global $APPLICATION;
		
		$orderId = $arOrderData['ID'];
	
		if($orderId > 0) {
			$order = Order::load($orderId);
			
			if($order) {
				$paymentCollection = $order->getPaymentCollection();
				
				if($paymentCollection) {
					foreach($paymentCollection as $payment) {
						if(!$payment->isPaid() && $payment->getField('PS_STATUS_CODE') == 'AUTHORIZED') {
							$paySystem = $payment->getPaySystem();
							if($paySystem && $paySystem->getField('ACTION_FILE') == 'modulbank') {
								$backUrl = $APPLICATION->GetCurPageParam();
								
								Bitrix\Main\Page\Asset::getInstance()->addString(
									'
									<script>
										BX.ready(function(){
											var pc = BX.findChildren(BX("tab_order_edit_table"), {"class": "adm-container-draggable", "attribute": {"data-id": "payment"}}, true);
											for(var i in pc) {
												if(pc.hasOwnProperty(i)) {
													var pci = BX.findChildren(pc[i], {"class": "adm-bus-pay"}, true);
													
													for(var j in pci) {
														if(pci.hasOwnProperty(j)) {
															if(pci[j].id !== undefined) {
																var cIndex = pci[j].id.substring(18);
																
																if(BX("PAYMENT_ID_" + cIndex).value == '.$payment->getId().') {
																	var dd = BX.findChildren(BX("payment_container_" + cIndex), {class: "payment-status"}, true);
											
																	for(var i in dd) {
																		if(dd.hasOwnProperty(i)) {
																			
																			var ddh = BX.create("div", {"attrs":{"className": "modulbank-payment-hold-control"}, "html": "'.Loc::getMessage('MODULBANK_PAYMENT_IS_HOLD').'. <a href=\"/bitrix/tools/modulbank.payments/hold_action.php?action=confirm&payment_id='.$payment->getId().'&order_id='.$orderId.'&'.bitrix_sessid_get().'&back_url='.$backUrl.'\" class=\"modulbank-payment-hold-button modulbank-payment-hold-button--confirm\">'.Loc::getMessage('MODULBANK_PAYMENT_HOLD_CONFIRM').'</a> '.Loc::getMessage('MODULBANK_PAYMENT_OR').' <a href=\"/bitrix/tools/modulbank.payments/hold_action.php?action=cancel&payment_id='.$payment->getId().'&order_id='.$orderId.'&'.bitrix_sessid_get().'&back_url='.$backUrl.'\" class=\"modulbank-payment-hold-button modulbank-payment-hold-button--cancel\">'.Loc::getMessage('MODULBANK_PAYMENT_HOLD_CANCEL').'</a>"});
																			BX.append(ddh, dd[i]);
																		}
																	}
																}
															}
														}
													}
												}
											}
										});
									</script>
									<style>
										.modulbank-payment-hold-control { margin-top: 5px; }
										.modulbank-payment-hold-button--confirm { color: green; }
										.modulbank-payment-hold-button--cancel { color: red; }
									</style>
									',
									Bitrix\Main\Page\AssetLocation::AFTER_JS
								);
							}
						}
					}
				}
			}
		}
	}
}
?>