<?php
/**
 * Created by IntelliJ IDEA.
 * User: GOOGLE
 * Date: 11/07/2020
 * Time: 10:25
 */

namespace App\Controller;

use App\Security\User\WebserviceUser;
use JMS\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\Security;

use Symfony\Component\Security\Core\User\UserProviderInterface;
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
    private $security;
    private $userProvider;
    private $urlGenerator;

    public function __construct(HttpClientInterface $client,
                                UserProviderInterface $userProvider,UrlGeneratorInterface $urlGenerator,
                                SerializerInterface $serialize,Security $security)
    {
        $this->client = $client;
        $this->serializer=$serialize;
        $this->security=$security;
        $this->userProvider=$userProvider;
        $this->urlGenerator = $urlGenerator;


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

        if(!is_object($data)){
            var_dump($request->getContent());
            echo("=============================");
            parse_str($request->getContent(),$output);
            $data=(object) $output;

        }
        var_dump($data);


        $this->saveToSession($data);
        if (!$this->security->isGranted('ROLE_USER')){
           $user_exist= $this->verifyUserExist($data);
           var_dump($user_exist);
           if(is_int($user_exist) && $user_exist==412){
               //initier la transaction et renvoyer la page operations
               $this->addFlash(
                   'error',
                   'compte à activer'
               );


               return $this->render('pages/operation.html.twig', ["transaction"=>$data,"activation_requested"=>true]);

           }
           if (is_int($user_exist) && $user_exist==404){
               return new RedirectResponse($this->urlGenerator->generate('register'));

           }


        }

        $params=$data;
        $endpoint="mobilemoney/".$data->id."/new";
        $headers=[
            'Accept' => 'application/json',
            "apikey"=> $this->apikey
        ];


        $response=$this->make_post_request((array)$params,$headers,$endpoint,(array)$data);


        $response=$this->handle_response($response);
        var_dump($response);
        /*
         * si les datas proviennent du formulaire renvoyer json sinon renvoyer la page operations
         */
        if(!isset($data->local)){
            $transaction=array_merge((array)$data,$response);
            return $this->render('pages/operationQs.html.twig',
                ["transaction"=>(array)$data,"transaction_status"=>$response]);

        }
        return $response;
    }

    public function updateAction(Request $request,$id)
    {
        $this->apikey = $this->getParameter('app.apikey');
        $this->site_url = $this->getParameter('app.site_url');


        $data = $request->getContent();
        var_dump($data);

        $data=json_decode($data);
        echo ("+++++++++++++++++++++");
        var_dump($data);
        var_dump($request->getMethod());


        $endpoint="mobilemoney/".$id;
        $headers=[
            'Accept' => 'application/json',
            "apikey"=> $this->apikey
        ];


        /*
         * recupère la transaction dans la session afin de recupérer le country code et le phone_number
         * de l'utilisateur ensuite essaye de le connecter un utilisant le mot de passe fourni
         */
        $session = $this->get('session');
        $transaction=$session->get('transaction');
        $login=[];
        $login["sender_phone"]=$transaction["sender_phone"];
        $login["sender_country_code"]=$transaction["sender_country_code"];
        /*
         * fake password
         */
        $login["password"]="";
        $user_exist=$this->verifyUserExist($login);

        /*
         * si la connexion a réussi l'opération continue
         */


        if( !is_object($user_exist)){

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

    public function start($transaction)
    {
        return $this->render('pages/operation.html.twig', ["transaction"=>$transaction

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
    public function make_post_request(array $parameters, array $header, $endpoint,$body)
    {

        $params = "";
        foreach ($parameters as $key => $value) {
            $params = $params . $key . "=" . $value . "&";
        }
        $params = substr($params, 0, -1);
        try {
            $response = $this->client->request(
                'POST',
                $this->site_url . $endpoint . "?" . $params,
                [
                    'headers' => $header,
                    'json' => $body
                ]
            );

            if($response->getStatusCode()==200 || $response->getStatusCode()==201 ||  $response->getStatusCode()==202){

                return $response->toArray();
            }
                    if($response->getStatusCode()==400 ){
                        $res=[
                            "id"=>1,
                            "status"=>"confirm_requested",
                            "created_at"=> "2020-07-13T16:33:28.544Z"
                        ];

                        return $res;
                    }
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

    private function saveToSession($transaction)
    {
        $this->get('session')->set('transaction',$transaction);
    }

    /*
     * vérifie que l'utilisateur existe et le redirige
     */
    private function verifyUserExist($data)
    {
        $login= new WebserviceUser();
        $data=(array)$data;
        $login->setCountryCode($data["sender_country_code"] );
        $login->setPhoneNumber($data["sender_phone"]);
        if (isset($data["password"])){
            $login->setPassword($data["password"]);

        }else{
            $login->setPassword("11");
        }
        try{
            $user= $this->userProvider->loadUserByUsername($login);
            return $user;

        }catch (CustomUserMessageAuthenticationException $exception){
            var_dump($exception->getCode());

            if($exception->getCode()==404){
                $this->addFlash(
                    'notice',
                    'in order to do this operation you must create an account. Create a daticash account.'
                );
                return 404;

            }
            if($exception->getCode()==412 ){
                $this->addFlash(
                    'notice',
                    'in order to do this operation you must Activate your account.'
                );
                return 412;

            }
            if($exception->getCode()==401){

                return 401;

            }
            if (!($exception->getCode()==401 || $exception->getCode()==412 || $exception->getCode()==404)){
                var_dump("++++++++++");
                var_dump($exception->getCode());

                throw new CustomUserMessageAuthenticationException(
                    ' A problem occurred during your connection! Check that you have entered your information correctly',[],$exception->getCode());

            }
        }

    }
}
