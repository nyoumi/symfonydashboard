<?php

/**
 * 
 */
class Dati_API
{
	private $api_key;
	
	public function __construct(string $secret, string $api_key)
	{
		$this->api_key = $api_key;
		$this->secret = $secret;
	}

	public function api_dati(string $url, $curl_data=null)
	{

	    //$url = "http://intranet.daticloud.com/htdocs/api/index.php/categories/4/".$endpoint;

    
	$options = array(
	    CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
        CURLOPT_HTTPHEADER => [$this->secret.":".$this->api_key],
        CURLOPT_RETURNTRANSFER => true,         // return web page
        CURLOPT_HEADER         => false,        // don't return headers
        CURLOPT_FOLLOWLOCATION => true,         // follow redirects
        CURLOPT_ENCODING       => "",           // handle all encodings
        CURLOPT_USERAGENT      => "spider",     // who am i
        CURLOPT_AUTOREFERER    => true,         // set referer on redirect
        CURLOPT_CONNECTTIMEOUT => 120,          // timeout on connect
        CURLOPT_TIMEOUT        => 120,          // timeout on response
        CURLOPT_MAXREDIRS      => 10,           // stop after 10 redirects
        CURLOPT_POST           => 1,            // i am sending post data
        CURLOPT_POSTFIELDS     => $curl_data,    // this are my post vars
        CURLOPT_SSL_VERIFYHOST => 0,            // don't verify ssl
        CURLOPT_SSL_VERIFYPEER => false,        //
        CURLOPT_VERBOSE        => 1                //
    );


    $ch      = curl_init($url);
    curl_setopt_array($ch,$options);
    $content = curl_exec($ch);
    $err     = curl_errno($ch);
    $errmsg  = curl_error($ch) ;
    $header  = curl_getinfo($ch);
    curl_close($ch);


			$header['errno']   = $err;
			$header['errmsg']  = $errmsg;
			$header['content'] = $content;

$myJsonResult = myJsonResult($header['content'], strpos($header['content'], "{", 0), strrpos($header['content'], "}", 0))."}";
		$response = json_decode($myJsonResult, true);		
			
			
    return $response;

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


	public function simple_curl($url, array $data=null){
		

		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

		// execute!
		$response = curl_exec($ch);

		// close the connection, release resources used
		curl_close($ch);

		return $response;
	}
	
	public function sampleGet($url){
		$ch = curl_init();  
	 
		curl_setopt($ch,CURLOPT_URL,$url);
		curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
	//  curl_setopt($ch,CURLOPT_HEADER, false); 
	 
		$output=curl_exec($ch);
	 
		curl_close($ch);
		return $output;
	}

	public function post_curl($url, array $data=null){
		
		$tuCurl = curl_init($url);
		
		curl_setopt($tuCurl, CURLOPT_HEADER, 0);
		curl_setopt($tuCurl, CURLOPT_POST, 1);
		curl_setopt($tuCurl, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($tuCurl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($tuCurl, CURLOPT_POSTFIELDS, $data);

		// execute!
		$response = curl_exec($ch);

		// close the connection, release resources used
		curl_close($ch);

		return $response;
	}

	public function api_coCart(string $endpoint, array $data=null, string $method, string $secret)
	{
		$ch = curl_init();

		curl_setopt_array( $ch, array(
		  CURLOPT_URL => "http://localhost/daticloud/wp-json/cocart/".$endpoint,
		  CURLOPT_CUSTOMREQUEST => $method,
		  CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
           CURLOPT_HTTPHEADER => [$secret.':' .$this->api_key],
		  CURLOPT_RETURNTRANSFER => true
		) );

		if ($data != null) {
	   	curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
	   }

		$result = curl_exec($ch);


		//$response = json_decode($result);

		curl_close($ch);

		return $response;
	}

	public function api_whois(string $endpoint, array $data=null, string $domainName)
	{

	$headers = array();

	    $url = 'https://www.whoisxmlapi.com/whoisserver/WhoisService?apiKey=at_oy0uieO3zz3psCMAbQpSWBjQt6yKP&outputFormat=JSON&domainName='.$domainName;
    
     	$ch = curl_init();

	   curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_HEADER, 0);


		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

	   $result = curl_exec($ch);

	   $response = json_decode($result, true);

	   curl_close($ch);


	   return $response;
	}


	public function api_godaddy(string $url, array $data=null, string $api_secret)
	{

	    $url = $url;

	    $header = array(
		            'Authorization: sso-key '.$this->api_key.':'.$api_secret
		        );
    
     	$ch = curl_init($url);

        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);

        if ($data != null) {
	   		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
	   	}
            
       $result = curl_exec($ch);

	   $response = json_decode($result, true);

	   curl_close($ch);

	   /*if (property_exists($response, 'error')) {
	   	throw new Exception($response->error->message);
	   	
	   }*/

	   return $response;
	}

}

function myJsonResult($result, $start, $end)
{
  return substr($result, $start, $end - $start);
}
?>