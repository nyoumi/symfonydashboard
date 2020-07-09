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

if (isset($_POST['captcha_token'])) {
	$captcha_token = $_POST['captcha_token'];

	$google_token = "6LcgU9sUAAAAAGp1dmFwxuZ3TwMq3TzrtJBGZJAz";

	$data = array("secret" => "6LcgU9sUAAAAAGp1dmFwxuZ3TwMq3TzrtJBGZJAz", "response" => $captcha_token);

	$googleCaptcha = new Dati_API($google_token);

	$captcha = $googleCaptcha->post_curl('https://www.google.com/recaptcha/api/siteverify', $data);

	if (!empty($captcha)) {
		if($captcha['success']){
			return true;
		} else {
			return false;
		}
	}
}

if (isset($_POST['action'])) {
	$data = [];
	if ($_POST['action'] == 'login') {
		
		if(isset($_POST['username']) && isset($_POST['password'])){

			if ($_POST['username'] != ''  && $_POST['password'] != '') {
				
			$data = [];
				$username = $_POST['username'];
				$password = $_POST['password'];
				$socketclienttype = $_POST['socketclienttype'];

  
				$curl_data = '{"socketclienttype":"'.$socketclienttype.'","type":"login","username":"'.$username.'","password":"'.$password.'"}';
				$url = "https://api.daticloud.com/secure/login";
				$dati_login = $datiObject->api_dati($url,$curl_data);
			 	 				 
			//$dati_login['response']['message'] = "confirm-token-form";//test
			//$dati_login['response']['user_id'] = "38";//test
			
			//$dati_login['token'] = "3df5585df366fe55dfdsf";//test

				if (!empty($dati_login)) {
					if(isset($dati_login['token'])){				
						$_SESSION = [
							'userToken' => $dati_login['token'],
						];
/* 								
						if (isset($devToken['devToken'])){
							$_SESSION['devToken'] = $dati_login['devToken'];
						}
						
						if (isset($busToken['busToken'])){
							$_SESSION['busToken'] = $devToken['busToken'];
						}
						
						$dati_login['userid'] = 12; // information à obtenir de l'API
*/
						$data['match'] = true;
						$data['details'] = $dati_login;	
						
					}else{
						if(isset($dati_login['response'])){
							switch ($dati_login['response']['message']) {							
								case "confirm-token-form":
										$data['match'] = $dati_login['response']['message'];
										$data['details'] = $dati_login['response']['message'];
										$data['user_id'] = $dati_login['response']['user_id'];
										
									break;
							}					
						}else{
							switch ($dati_login['error']['message']) {
								case "non-existent-account":
										$data = [
											'match' => false,
											'details' => "User Not Found! Try again...",
											'error_code' => "non-existent-account"
										];
									break;
									
								case "bad-credentials":
										$data = [
											'match' => false,
											'details' => "Login Bad ...",
											'error_code' => "login-bad"
										];
									break;
									
								case "login-lock":
										$data = [
											'match' => false,
											'details' => "Sorry your Account is lock ...",
											'error_code' => "login-lock"
										];
									break;

							}
						}
					}						
				}
				
				echo json_encode($data);
			}
		} 
	} 

	if ($_POST['action'] == 'create_account') {
		if(isset($_POST['username']) && isset($_POST['email']) && isset($_POST['password'])){

			if ($_POST['username'] != ''  && $_POST['password'] != ''  && $_POST['email'] != '') {
			$data = [];
				$username = $_POST['username'];
				$email = $_POST['email'];
				$password = $_POST['password'];
				$socketclienttype = $_POST['socketclienttype'];
				$type = 'register-form';
			//$role = 'userRole|devRole|busRole'; => add role when user create account


				$curl_data = '{"socketclienttype":"'.$socketclienttype.'","type":"'.$type.'","username":"'.$username.'","email":"'.$email.'", "password":"'.$password.'"}';
				$url = "https://api.daticloud.com/api/anon/daticash/user";
				$dati_login = $datiObject->api_dati($url,$curl_data);	

				if (!empty($dati_login)) {
					if(isset($dati_login['response'])){
						
							$data['match'] = $dati_login["response"]["message"];
							$data['details'] = $dati_login["response"]["user_id"];
					}else{			
						switch ($dati_login['error']['message']) {
								
							case "bad":
									$data = [
										'match' => true,
										'details' => "error resquest at: "
									];
									foreach($dati_login['keys'] as $key-> $value){
										$data['details'] = $data['details'].' ,'.$value;
									}
								break;
								
							case "already":
									$data = [
										'match' => true,
										'details' => "User with this email already exist."
									];
								break;

						}
					}						
				}
				
				echo json_encode($data);			
			
			
			}		
		
		
		}

	}

	if ($_POST['action'] == 'logout'){

		//unset($_SESSION['customer_id']);
		// remove all session variables
		session_unset();

		// destroy the session
		session_destroy();
		//session_destroy();
		$data["match"] = true;
		$data["details"] = "Login out succefully!";
		echo json_encode($data);
	}

	if ($_POST['action'] == 'chek-login'){
		if(isset($_POST['msg']) && $_POST['msg'] != ''){
			
				$curl_data = $_POST['msg'];
				
				$url = "https://api.daticloud.com/get/hosting";
				$data = $datiObject->api_dati($url,$curl_data);
				
			echo json_encode($data);
		}
	}

	if ($_POST['action'] == 'confirm-token-code') {
		if(isset($_POST['token']) && isset($_POST['user_id'])){

			if ($_POST['token'] != ''  && $_POST['user_id'] != '') {
				
				$user_id = $_POST['user_id'];
				$token = $_POST['token'];
				$by = $_POST['by'];
				
				if(isset($_POST['verify'])){
					$verify = $_POST['verify'];
					$curl_data = '{"verify":"'.$verify.'","token":"'.$token.'","by":"'.$by.'"}';
					
					$url = "https://api.daticloud.com/api/anon/activate/account/".$user_id."/".$token;
					$data_confirm_token_code = $datiObject->api_dati($url,$curl_data);					
				}else{
	
					$url = "https://api.daticloud.com/api/anon/activate/account/".$user_id."/".$token;
					$data_confirm_token_code = $datiObject->api_dati($url);
				}
				//var_dump($data_confirm_token_code);
/* 				
				$curl_data = '{"socketclienttype":"'.$socketclienttype.'"}';
				$url = "https://api.daticloud.com/api/anon/activate/account/".$user_id."/".$token;
				$data_confirm_token_code = $datiObject->api_dati($url,$curl_data); */
				
				if (!empty($data_confirm_token_code)) {
				    if (isset($data_confirm_token_code["response"])) {
						$data['match'] = true;
						$data['details'] = $data_confirm_token_code["response"]["details"];			        
				    }else{
						$data['match'] = false;
						$data['details'] = $data_confirm_token_code["error"]["details"];					        
				    }
				}					
				echo json_encode($data);
			}
		} 
	}  

	if ($_POST['action'] == 'resend-token-code') {//good
		if(isset($_POST['user_id']) && $_POST['user_id'] != ''){
		
				$user_id = $_POST['user_id'];
				
//$data_resend_token_code['waiting_time'] = "waiting_time";//test

				$url = "https://api.daticloud.com/api/anon/send/activation-sms/".$user_id;
				$data_resend_token_code = $datiObject->api_dati($url);
				if (!empty($data_resend_token_code)) {
					if ($data_resend_token_code['waiting_time']) {
								$data['match'] = false;//test
								$data['time_left'] = $data_resend_token_code['waiting_time'];//test	
					}	
				}
				echo json_encode($data);
		} 
	}	
		
	if ($_POST['action'] == 'active_email') {//good
		if(isset($_POST['user_id']) && $_POST['user_id'] != ''){

				$user_id = $_POST['user_id'];
				
//$data_active_email['response']['message'] = "The token email has been sent";//test

				if(isset($_POST['verify'])){
					$verify = $_POST['verify'];
					$curl_data = '{"verify":"'.$verify.'"}';
					
					$url = "https://api.daticloud.com/api/anon/send/activation-email/".$user_id;
					$data_active_email = $datiObject->api_dati($url,$curl_data);					
				}else{
					$url = "https://api.daticloud.com/api/anon/send/activation-email/".$user_id;
					$data_active_email = $datiObject->api_dati($url);
				}
				
				if (!empty($data_active_email)) {
					if ($data_active_email['response']['message']) {
								$data['match'] = true;//test
								$data['details'] = $data_active_email['response']['message'];//test
					}	
				}
				echo json_encode($data);
		} 
	}	
	
	if ($_POST['action'] == 'init_transaction') {
		
		if(isset($_POST['sub_request']) && $_POST['sub_request'] != ''){
				$data = [];
				
				$sub_request = $_POST['sub_request'];
				
				$curl_data = $sub_request;
				$url = "https://api.daticloud.com/api/anon/job/";
				$data_init_transaction = $datiObject->api_dati($url,$curl_data);
				
				//$url = "https://api.daticloud.com/api/anon/job/?sub_request=".$sub_request;
				//$data_init_transaction = $datiObject->httpGet($url);
				
				$data_init_transaction['response']['datails'] = "init transaction ok";//fake value
				$data_init_transaction['response']['transaction_id'] = "12456id";//fake value

				if (!empty($data_init_transaction)) {
					if (isset($data_init_transaction["response"])) {
						$data['match'] = false;
						$data['details'] = $data_init_transaction["response"];			        
					}else{
						$data['match'] = false;
						$data['details'] = $data_init_transaction["error"]["details"];					        
					}
				}	
									
				echo json_encode($data);

		} 
	}
	
	if ($_POST['action'] == 'check_status_transaction') {

		if(isset($_POST['temporary_user_token']) && isset($_POST['transaction_id'])){
				
				$temporary_user_token = $_POST['temporary_user_token'];
				$transaction_id = $_POST['transaction_id'];

				$data = [];

 				$curl_data = '{"token":"'.$temporary_user_token.'", "transaction_id":"'.$transaction_id.'"}';
				$url = "https://api.daticloud.com/api/job/";
				$data_check_status_transaction = $datiObject->api_dati($url,$curl_data);
 
		$data_check_status_transaction['response']['error'] = [
			'status' => "confirm",##ok|processing|confirm|failed
			'details' => "approved",##|declined|error|held for review|noresponse##
		];	//test 
		
//var_dump($data_check_status_transaction);

				if (!empty($data_check_status_transaction)) {
				    if (isset($data_check_status_transaction["response"])) {
						switch ($data_check_status_transaction['error']['status']) {
							case "processing":
							case "confirm":
							case "failed":
									$data['match'] = false;
									$data['error_code'] = $data_check_status_transaction['error']['message'];
									$data['details'] = $data_check_status_transaction['error']['details'];
								break;
						}				        
				    }else{
						$data = [
							'match' => true,
							'details' => $data_check_status_transaction['response'],
						];				        
				    }
				}
			
				echo json_encode($data);

		}

	}
	
	if ($_POST['action'] == 'reset_password') {
		if(isset($_POST['email']) && $_POST['email'] != ''){
			
				$email = $_POST['email'];
				$socketclienttype = $_POST['socketclienttype'];

				$curl_data = 'object={"socketclienttype":"'.$socketclienttype.'","type":"reset-password","email":"'.$email.'"}';
				$url = "https://api.daticloud.com/get/hosting";
				$data = $datiObject->api_dati($url,$curl_data);
			
				echo json_encode($data);
			
		} 
	} 

	if ($_POST['action'] == 'check_session') {
			$resultat["session_status"] = false;
			$resultat["private_page_status"] = true;

			if(isset($_SESSION['dati-private-page']) && $_SESSION['dati-private-page'] == true){
				$resultat["private_page_status"] = true;
			}
			
			if(isset($_SESSION['userToken'])){
				$resultat["session_status"] = true;
				$resultat["sessionid"] = $_SESSION['userToken'];
			}
			
			echo json_encode($resultat);	

	}
	
	if ($_POST['action'] == 'change_role') {
		//test
		if(!isset($_SESSION['userToken'])){

			if ($_POST['role'] != '') {
				
				//$token = $_SESSION['userToken'];
				//$role = $_POST['role'];
				$data = [];
				$token = "S54sd5sd47erf5fdSDe455sdL";
				$role = $_POST['role'];
				$socketclienttype = $_POST['socketclienttype'];

				$curl_data = 'object={"socketclienttype":"'.$socketclienttype.'","type":"confirmChangeRole","role":"'.$role.'","token":"'.$token.'"}';
				$url = "https://api.daticloud.com/get/hosting";
				$data_confirm_change_role = $datiObject->api_dati($url,$curl_data);

									$data_confirm_change_role['value'] = "ok";//fake value

				if (!empty($data_confirm_change_role)) {
					switch ($data_confirm_change_role['value']) {
						case "ok":																
								$data['match'] = true;
								$data['details'] = $data_confirm_change_role;
							break;
							
						case "bad":
								$data = [
									'match' => false,
									'details' => "Error occurred, try again later...",
									'error_code' => "bad"
								];
							break;
					}						
				}				
				echo json_encode($data);

			}
		}

	}

/////////////////////////////////////////////////////////////////////////////////////////////////////

	if ($_POST['action'] == 'check_ip') {

		if(isset($_POST['client_ip'])){
			$data = [];
			
			$client_ip = $_POST['client_ip'];		
   			
 				$url = "http://api.ipstack.com/". $client_ip ."?access_key=113b78668a764f6b345902b6d1423caf";
				$data_check_ip = $datiObject->httpGet($url);
 
				$data_check_ip = [
					"continent_code" => "AF",
				] ;  
 
				//{"ip":"154.72.167.74","type":"ipv4","continent_code":"AF","continent_name":"Africa","country_code":"CM","country_name":"Cameroon","region_code":"CE","region_name":"Centre","city":"Yaound\u00e9","zip":null,"latitude":3.8655600547790527,"longitude":11.534170150756836,"location":{"geoname_id":2220957,"capital":"Yaound\u00e9","languages":[{"code":"en","name":"English","native":"English"},{"code":"fr","name":"French","native":"Fran\u00e7ais"}],"country_flag":"http:\/\/assets.ipstack.com\/flags\/cm.svg","country_flag_emoji":"\ud83c\udde8\ud83c\uddf2","country_flag_emoji_unicode":"U+1F1E8 U+1F1F2","calling_code":"237","is_eu":false}};// test
				//test
				//var_dump($data_check_ip);
				if (!empty($data_check_ip)) {
					if($data_check_ip["continent_code"] == "AF"){
						$data = [
							'match' => true,
							'details' => $data_check_ip,
						];	
					}else{
						$data = [
							'match' => false,
							'details' => $data_check_ip,
						];
					}					
				}										
				echo json_encode($data);

		}

	}

	if ($_POST['action'] == 'daticash_login') {//good
		
		if(isset($_POST['phone_number']) && isset($_POST['pin_code']) && isset($_POST['country_code'])){

			if ($_POST['phone_number'] != ''  && $_POST['pin_code'] != '' && $_POST['country_code'] != '') {
//var_dump($_POST); 				
			$data = [];
				$register_if_not_exists = "1";
				$phone_number = $_POST['phone_number'];
				$pin_code = $_POST['pin_code'];
				$country_code = $_POST['country_code'];
				$socketclienttype = $_POST['socketclienttype'];

				
			$curl_data = [
				"phone_number" => $_POST['phone_number'],
				"pin_code" => $_POST['pin_code'],
				"country_code" => $_POST['country_code']
			];

				//$curl_data = '{"socketclienttype":"'.$socketclienttype.'","type":"login","phone_number":"'.$phone_number.'","country_code":"'.$country_code.'","register_if_not_exists":"'.$register_if_not_exists.'","pin_code":"'.$pin_code.'"}';
				$url = "https://api.daticash.com/api/user/authenticate?phone_number=".$curl_data['phone_number']."&password=".$curl_data['pin_code']."&country_code=".$curl_data['country_code'];
				$dati_login = $datiObject->httpGet($url);
				
				
var_dump($url); 			 	 				 
var_dump($dati_login); 	//die;	
/*	 	 				 
			$dati_login['message'] = "confirm-token-form";//test
			$dati_login['user_id'] = "38";//test			
			$dati_login['email'] = "exemple@mail.com";//test			
			$dati_login['token'] = "3df5585df366fe55dfdsf";//test
			$dati_login['sub_request'] = "3df5585df366fe55dfdsf";//test
			$dati_login['user_profile'] = "3df5585df366fe55dfdsf";//test
			$dati_login['path'] = "3df5585df366fe55dfdsf";//test
*/
				if (!empty($dati_login)) {
					switch (strtolower($dati_login['message'])) {							
						case "ok"://good
							$_SESSION = [
								'userToken' => $dati_login['token'],
								'userProfile' => $dati_login["user_profile"],
							];
							
								$data = [
									'match' => true,
									'details' => "Succefuly login! Redirection...",
									'success_code' => "login-ok",
									'user_token' => $dati_login['token'],
									'path' => $dati_login['path'],
									'sub_request' => $dati_login['sub_request'],
									'user_profile' => $dati_login["user_profile"],
								];
								
							break;	
							
						case "confirm-token-form":
								$data['match'] = true;
								$data['success_code'] = "confirm-token-form";
								
								$data['user_id'] = $dati_login['user_id'];
								$data['email'] = $dati_login['email'];
								
							break;
							
						case "account-non-existent"://good
								$data = [
									'match' => false,
									'details' => "User Not Found! Try again...",
									'error_code' => "non-existent-account"
								];
							break;
							
						case "bad-credentials"://good
								$data = [
									'match' => false,
									'details' => "Login Bad ...",
									'error_code' => "login-bad"
								];
							break;
							
						case "login-lock":
								$data = [
									'match' => false,
									'details' => "Sorry your Account is lock ...",
									'error_code' => "login-lock"
								];
							break;
/* 							
						case "bad-api-secret":
								$data = [
									'match' => false,
									'details' => "Api Key is missing from request headers.",
									'error_code' => "bad-api-secret"
								];
							break;	
								
						case "bad-api-key":
								$data = [
									'match' => false,
									'details' => "API Key does not exist.",
									'error_code' => "bad-api-key"
								];
							break;	 */
							//good
						default:
								$data = [
									'match' => false,
									'details' => $dati_login['message'],
									'error_code' => "not-define"
								];
							break;	

					}
					echo json_encode($data);
				}
	        } 
    	} 
	}
		
		if ($_POST['action'] == 'daticash_check_user_status') {//good
			if(isset($_POST['user_id']) && $_POST['user_id'] != ''){

				$curl_data = [ 
					'user_id' => $_POST['user_id']
				];
//var_dump($_POST);
					$url = "https://api.daticash.com/api/anon/user/".$curl_data["user_id"]."/is-enabled";
					$data_daticash_check_user_status = $datiObject->httpGet($url);
//var_dump($data_daticash_check_user_status['message']);
//var_dump($url);
			//$data_daticash_check_user_status["message"] ="1" ;	//test	
			
					if (!empty($data_daticash_check_user_status)) {
						switch ($data_daticash_check_user_status['message']) {
							case "1":	
									$data = [
										'match' => true,
										'message' => $data_daticash_check_user_status['message']
									];
								break;
								
							case "0":
									$data = [
										'match' => false,
										'message' => $data_daticash_check_user_status['message']										
									];
								break;
								
							default:
									$data = [
										'match' => false,
										'message' => $data_daticash_check_user_status['message']									
									];
									
						}	
					}	
					echo json_encode($data);;
				
			} 
		} 
		
		if ($_POST['action'] == 'daticash_request_reset_pin_code') {
			if(isset($_POST['email']) && $_POST['email'] != ''){
	//var_dump($_POST);
				$curl_data = [ 
					'email' => $_POST['email']
				];
					//$curl_data = 'object={"email":"'.$email.'"}';
					$url = "https://api.daticash.com/api/anon/request/reset-password?email=".$curl_data['email'];
					$data_daticash_reset_pin_code = $datiObject->httpGet($url);
	//var_dump($data_daticash_reset_pin_code);
				
	//$data_daticash_reset_pin_code['code'] = 400; //test			
	//$data_daticash_reset_pin_code['email'] = "Exemple@gameil.com"; // test
	//{"code":304,"message":"Whoops !!! Something Went wrong. Please try again later..."}"
	//{"code": 400,"message": "Attempted to call an undefined method named \"handle\" of class \"App\\UserManager\\Manager\".\nDid you mean to call e.g. \"handleChangePassword\" or \"handleResetPasswordRequest\"?"}"	
	//{"code": 400,"message": "No route found for \"GET /api/anon/request/reset-password\""}"
	//{"code":304,"message":"token-send-ok"}"
					if (!empty($data_daticash_reset_pin_code)) {
						switch ($data_daticash_reset_pin_code['code']) {
							case "201":	
									$data = [
										'match' => true,
										'details' => "A reset email has been sent to ".$data_daticash_reset_pin_code['email'],
										'success_code' => "http-created",
										
										'email' => $data_daticash_reset_pin_code['email']
									];
								break;
								
							case "304":	
									$data = [
										'match' => false,
										'details' => "Oops! Something went wrong; please try again later.",
										'error_code' => "http-not-modified"
									];
								break;
								
							case "400":
									$data = [
										'match' => false,
										'details' => $data_daticash_reset_pin_code['message'],
										'error_code' => ""
									];
								break;
								
							default:
									$data = [
										'match' => false,
										'details' => strtolower($data_daticash_reset_pin_code["message"]),
										'error_code' => "not-define"
									];
									
						}	
					}	
					echo json_encode($data);;
				
			} 
		} 
		
		if ($_POST['action'] == 'daticash_confirm_reset_pin_code') {
			if(isset($_POST['new_pin_code']) && isset($_POST['token']) && isset($_POST['user_id'])){
				if($_POST['new_pin_code'] != '' && $_POST['token'] != '' && $_POST['user_id'] != ''){

		//var_dump($_POST);
					$curl_data = [ 
						'user_id' => $_POST['user_id'],
						'new_password' => $_POST['new_pin_code'],
						'token' => $_POST['token'],
					];

						$url = "https://api.daticash.com/api/anon/apply/password/".$curl_data['token']."/".$curl_data['user_id']."/reset";
						$data_daticash_confirm_reset_pin_code = $datiObject->httpPost($url,$curl_data);

		//var_dump($data_daticash_confirm_reset_pin_code);
					
		//$data_daticash_confirm_reset_pin_code['code'] = 400; //test			
		//$data_daticash_confirm_reset_pin_code['email'] = "Exemple@gameil.com"; // test
				
						if (!empty($data_daticash_confirm_reset_pin_code)) {
							switch ($data_daticash_confirm_reset_pin_code['code']) {
								case "202":	
										// remove all session variables
										session_unset();

										// destroy the session
										session_destroy();
										
										$data = [
											'match' => true,
											'details' => "PIN Code successfully reseted",
											'success_code' => "password-reset-ok",
										];
									break;
																
								default:
										$data = [
											'match' => false,
											'details' => strtolower($data_daticash_confirm_reset_pin_code["message"]),
											'error_code' => "not-define"
										];
										
							}

							if(strtolower($data_daticash_confirm_reset_pin_code['message']) === "token_expired" || strtolower($data_daticash_confirm_reset_pin_code['message']) === "token-bad"){
								$data = [
											'match' => false,
											'details' => "Oops! your reset token has expired. Please try again. Note that the message is valid for 60 minutes.",
											'error_code' => "token_expired"
										];
							}			
						}	
						echo json_encode($data);;
				}
			} 
		} 

		if ($_POST['action'] == 'daticash_confirm_change_pin_code') {
			if(isset($_POST['old_pin_code']) && isset($_POST['new_pin_code']) && isset($_POST['token']) && isset($_POST['user_id'])){
				if($_POST['old_pin_code'] != '' && $_POST['new_pin_code'] != '' && $_POST['token'] != '' && $_POST['user_id'] != ''){

		//dump($_POST);
					$curl_data = [ 
						'new_password' => $_POST['new_pin_code'],
					];

						$url = "https://api.daticash.com/api/anon/apply/password/".$curl_data['token']."/".$curl_data['user_id']."/change";
						$data_daticash_confirm_change_pin_code = $datiObject->httpPost($url,$curl_data);

		//var_dump($data_daticash_confirm_change_pin_code);
					
		//$data_daticash_confirm_change_pin_code['code'] = 400; //test			
		//$data_daticash_confirm_change_pin_code['email'] = "Exemple@gameil.com"; // test
				
						if (!empty($data_daticash_confirm_change_pin_code)) {
							switch ($data_daticash_confirm_change_pin_code['code']) {
								case "201":	
										$data = [
											'match' => true,
											'details' => "A reset email has been sent succefully",
											'success_code' => "request-reset-ok",
											
											'email' => $data_daticash_confirm_change_pin_code['email']
										];
									break;
									
								case "400":
										$data = [
											'match' => false,
											'details' => "Oops ! the email provided is not associated with any account. Check and try again",
											'error_code' => "account-non-existent"
										];
									break;
									
								default:
										$data = [
											'match' => false,
											'details' => strtolower($data_daticash_confirm_change_pin_code["message"]),
											'error_code' => "not-define"
										];
										
							}	
						}	
						echo json_encode($data);;
				}
			} 
		} 


	if ($_POST['action'] == 'daticash_register_if_not_exist') {//good
		if(isset($_POST['phone_number']) && isset($_POST['country_code']) && isset($_POST['email']) && isset($_POST['pin_code'])){

			if ($_POST['phone_number'] != ''  && $_POST['pin_code'] != ''  && $_POST['email'] != '' && $_POST['pin_code'] != '') {
//var_dump($_POST);				
				$curl_data = [
					"phone_number" => $_POST['phone_number'],
					"country_code" => $_POST['country_code'],
					"password" => $_POST['pin_code'],
					"firstname" => $_POST['firstname'],
					"lastname" => $_POST['lastname'],
					"email" => $_POST['email'],
				];				

				$url = "https://api.daticash.com/api/anon/daticash/user";
				$dati_daticash_register_if_not_exist = $datiObject->httpPost($url,$curl_data);
 				

//var_dump($dati_daticash_register_if_not_exist);
/* 
$dati_daticash_register_if_not_exist['message'] = "confirm-token-form";
$dati_daticash_register_if_not_exist['sub_request'] = "";
$dati_daticash_register_if_not_exist['path'] = "";
$dati_daticash_register_if_not_exist['user_id'] = 21;
$dati_daticash_register_if_not_exist['email'] = "...ple@gmail.com";

 */				if (!empty($dati_daticash_register_if_not_exist)) {
						$data = [
							'match' => false,
							'details' => $dati_daticash_register_if_not_exist
						];					
					switch (strtolower($dati_daticash_register_if_not_exist['message'])) {
						case "confirm-token-form":
								$data = [
									'match' => true,
									'details' => "successfully registered ",
									'success_code' => "register-ok",
									
									'user_id' => $dati_daticash_register_if_not_exist['user_id'],									
									'email' => $dati_daticash_register_if_not_exist['email'],									
								];
							break;
							
						case "already":
								$data = [
									'match' => true,
									'details' => "User with this email already exist.",
									'success_code' => "already",
								];
							break;
							
						case "bad":
								$data = [
									'match' => false,
									'details' => $dati_daticash_register_if_not_exist['details'],
									'error_code' => "bad"
								];
							break;
							
						default:
								$data = [
									'match' => false,
									'details' => $dati_daticash_register_if_not_exist['message']
								];
							break;
					}
				}
				
				echo json_encode($data);			
			}		
		}
	}
		
		if ($_POST['action'] == 'daticash_request_sms_code') {//good
			if(isset($_POST['user_id']) && $_POST['user_id'] != ''){
			//var_dump($_POST);
					$user_id = $_POST['user_id'];
					
					//$data_resend_token_code['waiting_time'] = "waiting_time";//test

					$url = "https://api.daticash.com/api/anon/send/activation-sms/".$user_id;
					$data_resend_token_code = $datiObject->httpGet($url);
			//var_dump($data_resend_token_code);				
					if (!empty($data_resend_token_code)) {
						if ($data_resend_token_code['waiting_time']) {
									$data['match'] = false;//test
									$data['time_left'] = $data_resend_token_code['waiting_time'];//test	
									$data['details'] = $data_resend_token_code['message'];//test	
						}	
					}
					echo json_encode($data);
			} 
		}	

		if ($_POST['action'] == 'daticash_confirm_sms_token_code') {
			if(isset($_POST['token']) && isset($_POST['user_id'])){

				if ($_POST['token'] != ''  && $_POST['user_id'] != '') {
					
					$user_id = $_POST['user_id'];
					$token = $_POST['token'];
					$by = 'sms';
					
					if(isset($_POST['verify'])){
						$verify = $_POST['verify'];
						$curl_data = '{"verify":"'.$verify.'","token":"'.$token.'","by":"'.$by.'"}';
						
						$url = "https://api.daticash.com/api/anon/activate/account/".$user_id."/".$token;
						$data_confirm_token_code = $datiObject->httpGet($url,$curl_data);					
					}else{
		                //dump('not verify');
						$url = "https://api.daticash.com/api/anon/activate/account/".$user_id."/".$token;
						$data_confirm_token_code = $datiObject->httpGet($url);
					}
					//dump($data_confirm_token_code);
/*
					if (!empty($data_confirm_token_code)) {
						if (isset($data_confirm_token_code["response"])) {
							$data['match'] = true;
							$data['details'] = $data_confirm_token_code["response"]["details"];			        
						}else{
							$data['match'] = false;
							$data['details'] = $data_confirm_token_code["error"]["details"];					        
						}
					}
*/		

						switch (strtolower($data_confirm_token_code['message'])) {
							case "ok":
									$data = [
										'match' => true,
										'details' => $data_confirm_token_code["details"],
										'success_code' => "confirm-ok",									
									];
								break;
								
							case "bad":
									$data = [
										'match' => false,
										'details' => $data_confirm_token_code["details"],
										'success_code' => "confirm-bad",									
									];
								break;
								
							default:
									$data = [
										'match' => false,
										'details' => $dati_daticash_register_if_not_exist['details'],
									];
								break;
						}
						
						//dump($data);
					echo json_encode($data);
				}
			} 
		}  

		if ($_POST['action'] == 'init_mobilemoney_transaction') {
			
			if(isset($_POST['type']) && isset($_POST['account_ref']) && isset($_POST['amount_sent']) && isset($_POST['currency_sent']) && isset($_POST['recipient_phone']) && isset($_POST['recipient_country_code']) && isset($_POST['sender_phone']) && isset($_POST['sender_country_code']) && isset($_POST['reason'])){
var_dump($_POST);					
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
			var_dump($data_process_topup_by_mobilemoney);	
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
				
		if ($_POST['action'] == 'ckeck_transaction_status') {				
			if(isset($_POST['transaction_id'])){
				
				$action = $_POST['action'];				
				$transaction_id = $_POST['transaction_id'];	
										
					$url = "https://api.daticash.com/api/anon/topup/?action=".$action."&transaction_id=".$transaction_id;
					$data_process_topup_by_mobilemoney = $datiObject->httpGet($url);					
			
					if (!empty($data_process_topup_by_mobilemoney)) {
						switch (strtolower($data_process_topup_by_mobilemoney['message'])) {
							case "ok":
									$data = [
										'match' => true,
										'action' => $data_process_topup_by_mobilemoney["action"],										
										'details' => $data_process_topup_by_mobilemoney["details"],
										'success_code' => "transaction-ok",									
									];
								break;
								
							case "processing":
									$data = [
										'match' => false,
										'action' => $data_process_topup_by_mobilemoney["action"],										
										'details' => $data_process_topup_by_mobilemoney["details"],
										'success_code' => "transaction-processing",									
									];
								break;
								
							case "failed":
									$data = [
										'match' => false,
										'action' => $data_process_topup_by_mobilemoney["action"],										
										'details' => $data_process_topup_by_mobilemoney["details"],
										'success_code' => "transaction-failed",									
									];
								break;
								
							case "confirm":
									$data = [
										'match' => false,
										'action' => $data_process_topup_by_mobilemoney["action"],										
										'type' => $data_process_topup_by_mobilemoney["type"],
										'recipient_phone' => $data_process_topup_by_mobilemoney["recipient_phone"],
										'recipient_name' => $data_process_topup_by_mobilemoney["recipient_name"],
										'amount_received' => $data_process_topup_by_mobilemoney["amount_received"],
										'currency_received' => $data_process_topup_by_mobilemoney["currency_received"],
										'details' => $data_process_topup_by_mobilemoney["details"],
										'success_code' => "confirm-transaction",									
									];
								break;
								
							default:
									$data = [
										'match' => false,
										'details' => $data_process_topup_by_mobilemoney['details'],
									];
								break;
						}				
					}					
				echo json_encode($data);
			}
		} 
					
		if ($_POST['action'] == 'confirm_transaction_by_mobilemoney') {				
			if(isset($_POST['transaction_id'])){
				
				$action = $_POST['action'];				
				$transaction_id = $_POST['transaction_id'];	
										
					$url = "https://api.daticash.com/api/anon/topup/?action=".$action."&transaction_id=".$transaction_id;
					$data_process_topup_by_mobilemoney = $datiObject->httpGet($url);					
			
					if (!empty($data_process_topup_by_mobilemoney)) {
						switch (strtolower($data_process_topup_by_mobilemoney['message'])) {
							case "ok":
									$data = [
										'match' => true,
										'action' => $data_process_topup_by_mobilemoney["action"],										
										'details' => $data_process_topup_by_mobilemoney["details"],
										'success_code' => "transaction-ok",									
									];
								break;
								
							case "processing":
									$data = [
										'match' => false,
										'action' => $data_process_topup_by_mobilemoney["action"],										
										'details' => $data_process_topup_by_mobilemoney["details"],
										'success_code' => "transaction-processing",									
									];
								break;
								
							case "failed":
									$data = [
										'match' => false,
										'action' => $data_process_topup_by_mobilemoney["action"],										
										'details' => $data_process_topup_by_mobilemoney["details"],
										'success_code' => "transaction-failed",									
									];
								break;
								
							case "confirm":
									$data = [
										'match' => false,
										'action' => $data_process_topup_by_mobilemoney["action"],										
										'type' => $data_process_topup_by_mobilemoney["type"],
										'recipient_phone' => $data_process_topup_by_mobilemoney["recipient_phone"],
										'recipient_name' => $data_process_topup_by_mobilemoney["recipient_name"],
										'amount_received' => $data_process_topup_by_mobilemoney["amount_received"],
										'currency_received' => $data_process_topup_by_mobilemoney["currency_received"],
										'details' => $data_process_topup_by_mobilemoney["details"],
										'success_code' => "confirm-transaction",									
									];
								break;
								
							default:
									$data = [
										'match' => false,
										'details' => $data_process_topup_by_mobilemoney['details'],
									];
								break;
						}				
					}					
				echo json_encode($data);
			}
		} 
		

							
		if ($_POST['action'] == 'test') {				
														
					$url = "https://ntp.msn.com/edge/ntp?locale=fr&dsp=1&sp=Bing";

			$ch = curl_init();  
		 
			curl_setopt($ch,CURLOPT_URL,$url);
			curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
			curl_setopt($ch, CURLOPT_HTTPHEADER, [
			  'authority: ntp.msn.com',
			  'cache-control: max-age=0',
			  'accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9',
			  'upgrade-insecure-requests: 1',
			  'user-agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/83.0.4103.116 Safari/537.36 Edg/83.0.478.56',
			  'x-client-data: eyI0IjoiMjkwODE4NjE2NzkyNjI5NjUxMiIsIjUiOiJcIjUxSkY2d3h0amhLaU85Uk5wSFN0WE9zbjNNbkh2VGVzdGRNc215WVQzU2s9XCIiLCI2Ijoic3RhYmxlIn0=',
			  'sec-fetch-site: same-origin',
			  'sec-fetch-mode: same-origin',
			  'sec-fetch-dest: empty',
			  'accept-language: fr,fr-FR;q=0.9,en;q=0.8,en-GB;q=0.7,en-US;q=0.6',
			  'cookie: pglt-edgeChromium-ntp=547; sptmarket=fr||cm|fr-fr|fr-fr|fr; MSCC=1588965399; _EDGE_V=1; MUID=0E123ECE732B647A18953005729C65AD; ShCLSessionID=1593346467016_0.39363105448578595',
			]);	
			
			$output=curl_exec($ch);
			curl_close($ch);
			
					echo json_encode($output);
		dump($output);
		
		} 
					
		if ($_POST['action'] == 'test2') {				
														
					$url = "https://ntp.msn.com/resolver/api/resolve/v2/configindex/BBViXsS?targetScope=\{%22audienceMode%22:%22adult%22,%22browser%22:\{%22browserType%22:%22edgeChromium%22,%22version%22:%2283%22,%22ismobile%22:%22false%22\},%22deviceFormFactor%22:%22desktop%22,%22domain%22:%22ntp.msn.com%22,%22locale%22:\{%22language%22:%22fr%22,%22script%22:%22%22,%22market%22:%22fr%22\},%22os%22:%22windows%22,%22platform%22:%22web%22,%22pageType%22:%22ntp%22,%22pageExperiments%22:\[\]\}&apptype=edgeChromium&maxDepth=10&cbid=1'";

			$ch = curl_init();  

  
			curl_setopt($ch,CURLOPT_URL,$url);
			curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
			curl_setopt($ch, CURLOPT_HTTPHEADER, [
			  'Referer: https://ntp.msn.com/bundles/v1/edgeChromium/latest/web-worker.b5fe3f32e7ce4b46b674.js',
			  'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/83.0.4103.116 Safari/537.36 Edg/83.0.478.56',
			]);	
			
			$output=curl_exec($ch);
			curl_close($ch);
			
					echo json_encode($output);
		dump($output);
		
		} 
		
	
}

function dump($donnees){
	echo "<pre>";
	print_r($donnees);
	echo "</pre>";
}


?>