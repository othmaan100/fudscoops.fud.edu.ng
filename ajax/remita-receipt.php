<!-- 
@company - SystemSpecs
@product - Remita
@author - Oshadami Mike
-->
<?php
require_once('classes/Putme.php');
require_once('remita_constants.php');
date_default_timezone_set("Africa/Lagos");

$orderID = "";

$orderID = $_GET["orderID"];

if (isset($_POST['orderID'])){
$orderID = $_POST["order"];
//echo "SS".$orderID;
}
$response_code ="";
$rrr = "";
$response_message = "";


//$date = date("Y-m-d H:i:s");  
//Verify Transaction


function remita_transaction_details($orderId){
		$mert =  MERCHANTID;
		$api_key =  APIKEY;
		$concatString = $orderId . $api_key . $mert;
		$hash = hash('sha512', $concatString);
		$url 	= CHECKSTATUSURL . '/' . $mert  . '/' . $orderId . '/' . $hash . '/' . 'orderstatus.reg';
		//  Initiate curl
		$ch = curl_init();
		// Disable SSL verification
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		// Will return the response, if false it print the response
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		// Set the url
		curl_setopt($ch, CURLOPT_URL,$url);
		// Execute
		$result=curl_exec($ch);
		// Closing
		curl_close($ch);
		$response = json_decode($result, true);
		return $response;
	}
	if($orderID !=null){
		$response = remita_transaction_details($orderID);
		$response_code = $response['status'];
		if (isset($response['RRR']))
			{
			$rrr = $response['RRR'];
			}
		$response_message = $response['message'];
}
	 if($response_code == '01' || $response_code == '00')
	 { 
	 //sucess
		
		echo $response_code;
	 
	 }	
	else{ 
		//transaction not successfull	
		echo $response_code;
	}
