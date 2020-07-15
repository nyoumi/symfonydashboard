<?php
/**
 * Created by IntelliJ IDEA.
 * User: GOOGLE
 * Date: 11/07/2020
 * Time: 10:25
 */

namespace App\Controller;

use JMS\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;

use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;



class TransactionsController extends AbstractController
{
    private $client;
    private $site_url ;
    private $apikey ;
    private $user_id;
    private $serializer;

    public function __construct(HttpClientInterface $client,SerializerInterface $serialize)
    {
        $this->client = $client;
        $this->serializer=$serialize;

    }

    public function view($id)
    {
        $this->apikey = $this->getParameter('app.apikey');
        $this->site_url = $this->getParameter('app.site_url');
        $params=[];
        $endpoint="mobilemoney/".$id;
        $headers=[
            'Accept' => 'application/json',
            "apikey"=> $this->apikey
        ];

        $response=$this->make_get_request((array)$params,$headers,$endpoint);
        $response=$this->handle_response($response);
        return $response;

    }



    public function createAction(Request $request)
    {
        $this->apikey = $this->getParameter('app.apikey');
        $this->site_url = $this->getParameter('app.site_url');

        $data = $request->getContent();
        $data=json_decode($data);
        //var_dump($data);
        $params=$data;
        $endpoint="mobilemoney/".$data["id"]."/new";
        $headers=[
            'Accept' => 'application/json',
            "apikey"=> $this->apikey
        ];


        $response=$this->make_get_request((array)$params,$headers,$endpoint);
        /*
         * code retouné pour la vue afin de lire la réponse et de savoir qu'il ya eu succès de la requête
         * le content de la réponse doit contenir 201
         */
        $response=$this->handle_response($response);
        return $response;


    }

    public function updateAction(Request $request,$id)
    {
        $this->apikey = $this->getParameter('app.apikey');
        $this->site_url = $this->getParameter('app.site_url');

        $data = $request->getContent();
        $data=json_decode($data);
        //$params=$data;
       // $params["response"]="";
        //$params["step"]="";
        //$params["id"]=$id;
        $endpoint="mobilemoney/".$id;
        $headers=[
            'Accept' => 'application/json',
            "apikey"=> $this->apikey
        ];
        $session = $this->get('session');
        $password=$session->get('password');

        if( !($password==$data["password"])){

                $res =[
                    "code"=>(string)403,
                    "message" => 'erreur Authentification'
                ];


            return new Response(json_encode($res), Response::HTTP_OK,[
                "Content-Type"=>'application/json'
            ]);
        }


        $response=$this->make_put_request([],$headers,$endpoint,$data);

        $response=$this->handle_response($response);
        return $response;


    }

    public function start()
    {
        return $this->render('pages/operation.html.twig', [

        ]);
    }

    public function make_get_request(array $parameters, array $header, $endpoint)
    {

        $params = "";
        foreach ($parameters as $key => $value) {
            $params = $params . $key . "=" . $value . "&";

        }
        $params = substr($params, 0, -1);
        //$params = urlencode($params);

        try {
            $response = $this->client->request(
                'GET',
                $this->site_url . $endpoint . "?" . $params,
                [
                    'headers' => $header,
                ]
            );

            if($response->getStatusCode()==200 || $response->getStatusCode()==201){

                return $response->toArray();
            }
   /*         if($response->getStatusCode()==400 ){
                $res=[
                    "id"=>1,
                    "status"=>"string",
                    "created_at"=> "2020-07-13T16:33:28.544Z"
                    ];

                return $res;
            }*/
            else{
                return $response->getStatusCode();

            }




        } catch (TransportExceptionInterface |DecodingExceptionInterface
        |ClientExceptionInterface|RedirectionExceptionInterface|ServerExceptionInterface $e) {

            $this->addFlash(
                'error',
                'Une erreur interne s\'est produite veuillez réessayer plus tard'
            );
            return null;
        }


    }

    public function make_put_request(array $parameters, array $header, $endpoint,$body)
    {

        $params = "";
        foreach ($parameters as $key => $value) {
            $params = $params . $key . "=" . $value . "&";
        }
        $params = substr($params, 0, -1);
        try {
            $response = $this->client->request(
                'PUT',
                $this->site_url . $endpoint . "?" . $params,
                [
                    'headers' => $header,
                    'json' => $body
                ]
            );

            if($response->getStatusCode()==200 || $response->getStatusCode()==201 ||  $response->getStatusCode()==202){

                return $response->toArray();
            }
    /*        if($response->getStatusCode()==400 ){
                $res=[
                    "id"=>1,
                    "status"=>"string",
                    "created_at"=> "2020-07-13T16:33:28.544Z"
                ];

                return $res;
            }*/
            else{
                return $response->getStatusCode();

            }




        } catch (TransportExceptionInterface |DecodingExceptionInterface
        |ClientExceptionInterface|RedirectionExceptionInterface|ServerExceptionInterface $e) {

            $this->addFlash(
                'error',
                'Une erreur interne s\'est produite veuillez réessayer plus tard'
            );
            return null;
        }


    }

    /*
     * gère la réponse et la formate afin de retourner le json
     * @response réponse donnée par la méthode make request
     *
     */
    private function handle_response($response)
    {
        if(isset($response) && is_int($response)){
            $res =[
                "code"=>(string)$response
            ];
        }
        if(isset($response) && is_array($response)){
            $res = $response;
        }
        if(!isset($response)){
            $res =[
                "code"=>(string)0,
                "message" => 'erreur interne au serveur'
            ];
        }

        return new Response(json_encode($res), Response::HTTP_CREATED,[
            "Content-Type"=>'application/json'
        ]);
    }
}
