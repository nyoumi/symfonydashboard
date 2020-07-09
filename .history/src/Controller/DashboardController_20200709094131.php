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
        $this->site_url = $this->getParameter('app.site_url');

        
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
        } catch (\Throwable $th) {
            //throw $th;
        }
        
        $statusCode = $response->getStatusCode();
        $contents = $response->getContent();
        if($statusCode==200) return $response->toArray();
        return null;



    }
    public function view(): Response
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
            'end_at'=>'2021-01-01 00:00:00',
            'page'=> '1',
            'limit'=>'10'
        ];
        $this->transactions=$this->make_get_request($params,$headers,$endpoint);

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
        $endpoint="secure/login";
        $headers=[
            'Accept' => 'application/json',
        ];
        $params=[
            'pin_code' => 'asc',
            'phone_number' => '236978756',
            'country_code'=> '+237',

        ];
        try {
            $login=$this->make_get_request($params,$headers,$endpoint);
        var_dump($login);
        } catch (\Throwable $th) {
            //throw $th;
        }
       
        $this->accounts = [
            [
            'id'=> 12,
            'name'=>'Default',
            'balance'=> 250000,
            'description'=> 'The DatiCash Default Account'
           ],
            [
            'id'=> 14,
            'name'=> 'Easy School',
            'balance'=> 122000,
            'description'=> 'My School application account.'
           ]
            ];
            foreach ($this->accounts  as $account) {
                $this->total_balance=$this->total_balance+(float) $account["balance"];
     
            }

       if( isset($user)){
        $this->accounts = $user["accounts"];

        foreach ($this->accounts  as $account) {
            $this->total_balance=$this->total_balance+(float) $account["balance"];
 
        }
       }


       if( isset($this->transactions)){

        foreach ($this->transactions as $transaction) {
            if($transaction["type"]=="withdraw")
            {
                $this->withdraw_amount=$this->withdraw_amount+(float)$transaction["amount_sent"];

            }

            if($transaction["type"]=="deposit"){
                $this->deposit_amount=$this->deposit_amount+(float)$transaction["amount_sent"];

            }

        }
       }
       

       $layout = $twig->load('some_layout_template.twig');


       $container->loadFromExtension('twig', [
        // ...
        'globals' => [
            'uuid' => '@App\Generator\UuidGenerator',
        ],
    ]);
        return $this->render('pages/dashboard.html.twig', [
            'total_balance' =>$this->total_balance,
            'withdraw_amount' =>$this->withdraw_amount,
            'deposit_amount' =>$this->deposit_amount,
            'accounts' =>$this->accounts,
            'transactions' =>$this->transactions,


        ]); 
        
    }
}