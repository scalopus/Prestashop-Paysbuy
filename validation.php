<?php

require_once(dirname(__FILE__).'/../../config/config.inc.php');
require_once(dirname(__FILE__).'/paysbuy.php');

// Security Checking : The results from Paysbuy should be completed and not corrupted.
if (empty($_POST['result']) || empty($_POST['apCode']) || empty($_REQUEST['amt'])) {
	// Parameter is not completed. This script may be under attacking.
	header ('Status: 404 Not Found');
	exit ();
} else {
	//header ('Status: 200 OK');
}





// Value from untrusts identity which Act as Paysbuy Co. Ltd.
$payment_status	 = substr($_POST["result"], 0, 2);
$cartnumber = trim(substr($_POST["result"],2));
$amount = $_POST['amt'];
$psbRef = $_POST['apCode'];

// Create Common Log
$log = '';
$log .= "\nA[" . $_POST['result'] ."],[". $psbRef ."]\n";
$log .= "B[" . $_SERVER['REMOTE_ADDR'] . "]\t";
$log .= "C[" . microtime(true) . "]\n";
$log .= "A is Shop cart number / Paysbuy Reference.\nB is Untrusted IP Address\nC is Message received Time at the shop\n";


$AmountResult = 0;
$error = '';

if($payment_status =='00') {
	// Cross Checking with CURL 
	$AmountResult =paysbuy_recheck( Configuration::get('PAYSBUY_EMAIL'), $cartnumber, $_POST['apCode'],$amount); 
	if ($AmountResult == -1)
	{
		$error .= "The verification of this payment transaction is failed. Please contact PaySbuy Co. Ltd. for details.\n";
	}
	
} else {
	$error .= "The payment was not successful.\n";
}

$amount = $AmountResult; // Use money value that we directly retreive from the PaySbuy server.

$paysbuy = new Paysbuy();

if ($payment_status == '00' && $error == '') {
	
	// Success
	$currency = $paysbuy -> getCurrency();
	
	$c_id = (is_array($currency) ? $currency['id_currency'] : $currency->id);
	$c_rate = (is_array($currency) ? $currency['conversion_rate'] : $currency->conversion_rate);
	// Remove Gateway Charge
	 $amount = $amount * 100 / (Configuration::get('PAYSBUY_CHARGE')+100) ;
	
	if ($c_id != intval(Configuration::get('PS_CURRENCY_DEFAULT')))
		$amount *= 1/$c_rate ;
	
	$paysbuy->validateOrder($cartnumber, _PS_OS_PAYMENT_, $amount, $paysbuy->displayName, $log);
} else {
	$error .= $log;
	
}
if (!empty($error)) {
	$paysbuy->validateOrder(intval($cartnumber), _PS_OS_ERROR_, 0, $paysbuy->displayName, $error.'<br />');
}


// Original Source by : Siambox.com http://www.thaihosttalk.com/index.php?topic=19899.0
function paysbuy_recheck($psbmail, $cart, $psbRef, $amount=-1)
{
	$query = "invoiceNo=$cart&merchantEmail=$psbmail&strApCode=$psbRef";

	// Request URI (Secure)
	$ch = curl_init( "http://www.paysbuy.com/getinvoice/getinvoicestatus.asmx/GetInvoice");

	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, "$query");
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	$xmlResponse  = curl_exec($ch);
	curl_close($ch);


	$StatusResult		= XMLGetValue($xmlResponse, 'StatusResult');
	$AmountResult	= XMLGetValue($xmlResponse, 'AmountResult');
	$MethodResult	= XMLGetValue($xmlResponse, 'MethodResult');

	if ($StatusResult != 'Accept')
	{
		return -1; // Reject the payment.
	} else {
		// Bypass Amount of Payment Validation. Get check at final step with $paysbuy -> validateOrder.
		//if (!$AmountResult || $AmountResult != $amount)
		//{
		//	return false; // Amount of Money is not equals.
		//} else {
			return $AmountResult; // OK. Verified.
		//}
	}
}

function XMLGetValue($msg, $str)
{
	$str1 = "<$str>";
	$str2 = "</$str>";
	$start_pos = strpos($msg, $str1);
	$stop_post = strpos($msg, $str2);
	$start_pos += strlen($str1);
	return substr($msg, $start_pos, $stop_post - $start_pos);
}


?>