<?php

/**
 * 
 */
class Dati_API
{
	private $api_key;
	
	public function __construct(string $secret, string $api_key){
		$this->api_key = $api_key;
		$this->secret = $secret;
	}

	public function httpGet($url){
		$ch = curl_init();  
	 
		curl_setopt($ch,CURLOPT_URL,$url);
		curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, [$this->secret.":".$this->api_key]);	
	//  curl_setopt($ch,CURLOPT_HEADER, false); 
	 
		$output=curl_exec($ch);
	 
		curl_close($ch);
		
		//var_dump($output);
		//var_dump(strpos($output, "{", 0));

		if(strpos($output, "{", 0) !== false){
			//encode le resultat de type string en tableau
			$myJsonResult = myJsonResult($output, strpos($output, "{", 0), strrpos($output, "}", 0))."}";
			$output = json_decode($myJsonResult, true);		
		}
		//var_dump($output);
	return $output;		
		
	}

	public function httpPost($url,$params){
	  $postData = '';
	   //create name value pairs seperated by &
	   foreach($params as $k => $v) 
	   { 
		  $postData .= $k . '='.$v.'&'; 
	   }
	   $postData = rtrim($postData, '&');
	   
//var_dump($postData);	 

		$ch = curl_init();  
	 
		curl_setopt($ch,CURLOPT_URL,$url);
		curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
		curl_setopt($ch,CURLOPT_HEADER, false); 
		curl_setopt($ch, CURLOPT_HTTPHEADER, [$this->secret.":".$this->api_key]);			
		//curl_setopt($ch, CURLOPT_POST, count($postData));
		curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);

		$output=curl_exec($ch);
	 
		curl_close($ch);
		
		//var_dump($output);
		//var_dump(strpos($output, "{", 0));

		if(strpos($output, "{", 0) !== false){
			//encode le resultat de type string en tableau
			$myJsonResult = myJsonResult($output, strpos($output, "{", 0), strrpos($output, "}", 0))."}";
			$output = json_decode($myJsonResult, true);		
		}
		//var_dump($output);
	return $output;
	 
	}

}

	function myJsonResult($result, $start, $end){
	  return substr($result, $start, $end - $start);
	}
	



