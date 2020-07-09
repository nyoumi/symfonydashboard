<?php
// src/Controller/LuckyController.php
namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;



class DatiController extends Controller
{
    private $client;
    private $site_url ;
    private $apikey ;
    private $user_id;
    private $accounts=[];
    private $total_balance=0;



    public function __construct(HttpClientInterface $client)
    {
        $this->client = $client;

    }
    public function make_get_request(array $parameters,array $header,$endpoint){

        $params="";
        foreach($parameters as $key=>$value)
        {
            $params=$params.$key."=".$value."&";
            
        }
        $params = substr($params, 0, -1);
        $params=urlencode($params);
        $response = $this->client->request(
            'GET',
            $this->site_url.$endpoint."?".$params,
            [
                'headers' => $header,
            ]
        );
        $statusCode = $response->getStatusCode();
        $contents = $response->getContent();
        var_dump($contents)  ;
        if($statusCode==200) return $response->toArray();
        return null;



    }
    public function number(): Response
    {
        $this->apikey = $this->getParameter('app.apikey');
        $this->site_url = $this->getParameter('app.site_url');
        $this->user_id="23";

        $endpoint="mobilemoneys";
        $headers=[
            'Accept' => 'application/json',
            "apikey"=> $this->apikey
        ];
        $params=[
            'order' => 'asc',
            'user_id' => '23',
            'start_at'=> '2020-01-01 00:00:00',
            'end_at'=>'2020-01-01 00:00:00',
            'page'=> '1',
            'limit'=>'10'
        ];
        $_transactions=$this->make_get_request($params,$headers,$endpoint);

        $endpoint="user/".$this->user_id;
        $params=[];
        $user = $this->make_get_request($params,$headers,$endpoint);

        $params=[
            'user_id' => '23',
        ];
        $endpoint="services";
        $services = $this->make_get_request($params,$headers,$endpoint);

        $params=[
            'order' => 'asc',
            'user_id' => '23',
            'start_at'=> '2020-01-01 00:00:00',
            'end_at'=>'2020-01-01 00:00:00',
            'page'=> '1',
            'limit'=>'10'
        ];
        $endpoint="mobilemoneys";
        $_withdraw_amount= $this->make_get_request($params,$headers,$endpoint);
        $_deposit_amount= $this->make_get_request($params,$headers,$endpoint);
       if( isset($user)){
        $_accounts= $user["accounts"];

        foreach ($_accounts as $account) {
            $total_balance=$total_balance+(int)$account["balance"];
        }
       }
       
        

        return $this->render('dashboard/dashboard.html.twig', [
            '_locale' => $user["locale"],
            '_total_balance' => $total_balance,
            '_withdraw_amount' =>$withdraw_amount,
            '_deposit_amount' =>$deposit_amount,
            '_accounts' =>$accounts,
            '_transactions' =>$_transactions,
            '_services' => $services


        ]); 
        
    }
}