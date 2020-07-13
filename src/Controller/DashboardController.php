<?php
namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;



class DashboardController extends Controller
{
    private $client;
    private $site_url ;
    private $apikey ;
    private $user_id;
    private $accounts=[];
    private $transactions=[];

    private $total_balance=0;
    private $withdraw_amount=0;
    private $deposit_amount=0;



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
        try {
            $response = $this->client->request(
                'GET',
                $this->site_url.$endpoint."?".$params,
                [
                    'headers' => $header,
                ]
            );
            if($response->getStatusCode()==200){

                return $response->toArray();
            };
            if($response->getStatusCode()==400){
                $this->addFlash(
                    'error',
                    'Echec 400 !'.$endpoint
                );
                $resp=[
                    'code' => 400,

                ];
                return $resp;
            };
            if($response->getStatusCode()==404){
                $this->addFlash(
                    'error',
                    'Echec 404!'.$endpoint
                );
                $resp=[
                    'code' => 404,

                ];

                return $resp;
            };
            if($response->getStatusCode()==409){
                $this->addFlash(
                    'error',
                    'Echec 409 '.$endpoint
                );
                return  $resp=[
                    'code' => 409,

                ];
            };
        } catch (\Throwable $th) {
            //throw $th;
        }

        return null;



    }
    public function view(): Response
    {
        $this->apikey = $this->getParameter('app.apikey');
        $this->site_url = $this->getParameter('app.site_url');
        $this->user_id=$this->getUser()->getid();

        $endpoint="mobilemoneys";
        $headers=[
            'Accept' => 'application/json',
            "apikey"=> $this->apikey
        ];
        $params=[
            'order' => 'asc',
            'user_id' => $this->user_id,
            'start_at'=> '2020-01-01 00:00:00',
            'end_at'=>'2021-01-01 00:00:00',
            'page'=> '1',
            'limit'=>'10'
        ];
        try {
            $this->transactions=$this->make_get_request($params,$headers,$endpoint);
            if($this->transactions["code"]== 404){
                $this->transactions=[];
            }

        } catch (\Throwable $th) {
            //throw $th;
        }

        $endpoint="user/".$this->user_id;
        $params=[];
        try {
            $user = $this->make_get_request($params,$headers,$endpoint);

        } catch (\Throwable $th) {
            //throw $th;
        }
       
  /*       $params=[
            'user_id' => '23',
        ];
        $endpoint="services";

        $params=[
            'order' => 'asc',
            'user_id' => '23',
            'start_at'=> '2020-01-01 00:00:00',
            'end_at'=>'2020-01-01 00:00:00',
            'page'=> '1',
            'limit'=>'10'
        ]; */
        //$services = $this->make_get_request($params,$headers,$endpoint);

       


       if( isset($user)){
        $this->accounts = $user["accounts"];

        foreach ($this->accounts  as $account) {
            $this->total_balance=$this->total_balance+(float) $account["balance"];
 
        }
       }


       if( isset($this->transactions) && !isset($this->transactions["code"])){

        foreach ($this->transactions as $transaction) {
            if($transaction["type"]=="withdraw") {
                $this->withdraw_amount=$this->withdraw_amount+(float)$transaction["amount_sent"];

            }

            if($transaction["type"]=="deposit"){
                $this->deposit_amount=$this->deposit_amount+(float)$transaction["amount_sent"];

            }

        }
       }
       

       //$this->get('twig')->addGlobal('App.today', date('Y-m-d'));

        return $this->render('pages/dashboard.html.twig', [
            'total_balance' =>$this->total_balance,
            'withdraw_amount' =>$this->withdraw_amount,
            'deposit_amount' =>$this->deposit_amount,
            'accounts' =>$this->accounts,
            'transactions' =>$this->transactions,


        ]); 
        
    }
}