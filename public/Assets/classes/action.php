<?php						
session_start();
if(isset($_SESSION['customer_id'])){
	header("Location: ../index.php");
	exit;
}

		//session_unset();
		//session_destroy();die;
include("class-api.php");
$value_token = 'sec_5ed93806dd2ca';
$secret = 'apikey';

$datiObject = new Dati_API($secret, $value_token);

if (isset($_POST['action'])) {
	$data = [];
	
		if ($_POST['action'] == 'convert') {
			if(isset($_POST['currency_received']) && isset($_POST['amount_received']) && isset($_POST['currency_sent']) && isset($_POST['amount_sent'])){
				  $currency_received = $_POST['currency_received'];
				  $amount_received = $_POST['amount_received'];
				  $currency_sent = $_POST['currency_sent'];
				  $amount_sent = $_POST['amount_sent'];

				$curl_data = array( 
					"currency_received" => $currency_received,
					"amount_received" => $amount_received,
					"currency_sent" => $currency_sent,
					"amount_sent" => $amount_sent
				);
				
				$url = 'https://api.daticash.com/api/anon/pricing?currency_received='.$curl_data["currency_received"].'&amount_received='.$curl_data["amount_received"].'&currency_sent='.$curl_data["currency_sent"].'&amount_sent='.$curl_data["amount_sent"];
			//var_dump($url);		
				$dati_convert = $datiObject->httpGet($url);	
			//var_dump($dati_convert);
				if (!empty($dati_convert)) {
					if (isset($dati_convert["total_pay"])) {	
						$data["match"] = true;
						$data["details"] = $dati_convert;
					}else{
						$data["match"] = false;
						$data["details"] = "error conversion: try again or ignore, you can continuous you transaction";						
					}	
						
				}else{
						$data["match"] = false;
						$data["details"] = "error contact server: try again or ignore, you can continuous you transaction";					
				}
				
				echo json_encode($data);	
			}
		}				
			
		if ($_POST['action'] == 'init_mobilemoney_transaction') {
			
			if(isset($_POST['type']) && isset($_POST['account_ref']) && isset($_POST['amount_sent']) && isset($_POST['currency_sent']) && isset($_POST['recipient_phone']) && isset($_POST['recipient_country_code']) && isset($_POST['sender_phone']) && isset($_POST['sender_country_code']) && isset($_POST['reason'])){
//var_dump($_POST);					
				$curl_data = [
					"action" => $_POST['action'],
					"type" => $_POST['type'],
					"account_ref" => $_POST['account_ref'],
					"amount_sent" => $_POST['amount_sent'],
					"currency_sent" => $_POST['currency_sent'],
					"recipient_phone" => $_POST['recipient_phone'],
					"recipient_country_code" => $_POST['recipient_country_code'],
					"sender_phone" => $_POST['sender_phone'],
					"sender_country_code" => $_POST['sender_country_code'],
					"reason" => $_POST['reason'],
				];							
											
						$url = "https://api.daticash.com/api/mobilemoney/new";
						$data_process_topup_by_mobilemoney = $datiObject->httpPost($url, $curl_data);					
			//var_dump($data_process_topup_by_mobilemoney);	
					if (!empty($data_process_topup_by_mobilemoney)) {
						switch (strtolower($data_process_topup_by_mobilemoney['code'])) {
							case "201":
									$data = [
										'match' => true,
										'action' => $data_process_topup_by_mobilemoney["action"],										
										'details' => $data_process_topup_by_mobilemoney["details"],
										'mobilemoney_id' => $data_process_topup_by_mobilemoney["mobilemoney_id"],									
										'success_code' => "init-momo_trans-ok",									
									];
								break;
								
							default:
									$data = [
										'match' => false,
										'action' => $data_process_topup_by_mobilemoney["action"],										
										'details' => $data_process_topup_by_mobilemoney["message"],
										'error_code' => "init-momo_trans-failed",									
									];
								break;
						}				
					}				
					
					echo json_encode($data);
			}
		} 
				

}

function dump($donnees){
	echo "<pre>";
	print_r($donnees);
	echo "</pre>";
}


?>