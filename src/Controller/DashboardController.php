<?php
namespace App\Controller;

use DateInterval;
use DateTime;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Security;
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
    private $security;
    private $services;
    private $account_types;



    public function __construct(HttpClientInterface $client,Security $security)
    {
        $this->client = $client;
        $this->security=$security;

    }
    public function make_get_request(array $parameters,array $header,$endpoint){

        $params="";
        foreach($parameters as $key=>$value)
        {
            $params=$params.$key."=".$value."&";
            
        }
        $params = substr($params, 0, -1);
        //$params=urlencode($params);
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
            }else{
                /*$this->addFlash(
                    'error',
                    'Echec ! '.$response->getStatusCode()." ".$endpoint
                );*/
                $resp=[
                    'code' => $response->getStatusCode(),
                ];
                return $resp;
            }
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
        $user = $this->security->getUser();
        $user_roles=$user->getRoles();


        $endpoint="mobilemoney/list";
        $headers=[
            'Accept' => 'application/json',
            "apikey"=> $this->apikey
        ];
        $params=[
            'order' => 'desc',
            'user_id' => $this->user_id,
            'sender_phone' => $user->getPhoneNumber(),
            'starting_at'=> '2020-01-01 00:00:00',
            'ending_at'=>'2021-01-01 00:00:00',
            'page'=> '1',
            'limit'=>'10'
        ];
        try {
            $this->transactions=$this->make_get_request($params,$headers,$endpoint);
            if(!(isset($this->transactions) && isset($this->transactions[0]['id']))){
                $this->transactions=[];
            }

        } catch (\Throwable $th) {
            //throw $th;
        }

        $endpoint="user/".$this->user_id."/view";
        $params=[];
        try {
            $user = $this->make_get_request($params,$headers,$endpoint);

        } catch (\Throwable $th) {
            //throw $th;
        }
        $endpoint="mobilemoney/services/list";
        $params=[];

            $services = $this->make_get_request($params,$headers,$endpoint);
            //var_dump($services);
           // var_dump("+++++++++++++++++++++++++++++++++++++");
            if (!isset($services['code']) && isset($services)){


                for ($i = 0; $i < sizeof($services); $i++) {


                     if (isset($services[$i]["roles"])){

                         foreach ($services[$i]["roles"] as $role){
                             if (!in_array($role, $user_roles)) {
                                 array_splice($services,$i,1);
                             }
                         }

                     }
                    else{

                        for ( $j =0; $j < sizeof( array_values($services[$i]["services"]) ); $j++){
                            $services_dispo=array_values($services[$i]["services"]);


                        if (isset($services_dispo[$j]["roles"])){



                            foreach ($services_dispo[$j]["roles"] as $role){
                                if (!$this->security->isGranted($role))
                                   {
                                      array_splice($services[$i]["services"],$j,1);

                                  }
                              }

                          }
                      }
                  }
                }
               // var_dump($services);


            }
            $this->services=$services;

        $endpoint="accounts/types";
        $headers=[
            'Accept' => 'application/json',
            "apikey"=> $this->apikey
        ];

        $params=[];
        $account_types=$this->make_get_request($params,$headers,$endpoint);
        $this->account_types=$account_types;
        $session = $this->get('session');
        $session->set('services', $this->services);
        $session->set('account_types', $this->account_types);

        if( isset($user) && isset($user["id"])){

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

        return $this->render('pages/dashboard.html.twig', [
            'total_balance' =>$this->total_balance,
            'withdraw_amount' =>$this->withdraw_amount,
            'deposit_amount' =>$this->deposit_amount,
            'accounts' =>$this->accounts,
            'transactions' =>$this->transactions,
            'services' =>$this->services


        ]); 
        
    }

    public function viewHistory(Request $request)
    {
        $this->apikey = $this->getParameter('app.apikey');
        $this->site_url = $this->getParameter('app.site_url');
        $this->user_id=$this->getUser()->getid();
        $user = $this->security->getUser();
        $user_roles=$user->getRoles();
        $data=$request->request->all();
        $this->view();

        $endpoint="mobilemoney/list";
        $headers=[
            'Accept' => 'application/json',
            "apikey"=> $this->apikey
        ];
        $now = new DateTime( 'now');
        //echo $now->format('Y-m-d H:i:s');
        $limitDate=$now->format('Y-m-d H:i:s');
        try {
            $limitDate=$now->sub(new DateInterval('P30D'));
           // echo $limitDate->format('Y-m-d H:i:s');

        } catch (\Exception $e) {
        }

            //echo $now;
        $params=[
            'order' => 'asc',
            'user_id' => $this->user_id,
            'sender_phone' =>$user->getPhoneNumber(),
            'start_at'=> $now->format('Y-m-d H:i:s'),
            'end_at'=>$limitDate->format('Y-m-d H:i:s'),
            'page'=> '1',
            'limit'=>'100'
        ];

        if(isset($data)){
            if (isset($data["starting_at"])) $params["start_at"]=$data["starting_at"];
            if (isset($data["ending_at"])) $params["end_at"]=$data["ending_at"];

        }
        try {
            $this->transactions=$this->make_get_request($params,$headers,$endpoint);
            if(!(isset($this->transactions) && isset($this->transactions[0]['id']))){
                $this->transactions=[];
            }
        } catch (\Throwable $th) {
            //throw $th;
        }

        return $this->render('pages/history.html.twig', [
            'transactions' =>$this->transactions,
        ]);

    }
}