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
        $response=$this->handle_response($response,false);
        return $response;

    }



    public function createAction(Request $request)
    {
        return new RedirectResponse($this->urlGenerator->generate('register'));

        $this->apikey = $this->getParameter('app.apikey');
        $this->site_url = $this->getParameter('app.site_url');

        $data = $request->getContent();
        //echo("=============================");



        $data=json_decode($data);

        if(!is_object($data)){
            parse_str($request->getContent(),$output);
            $data=(object) $output;

        }


        $this->saveToSession($data);
           $user_exist= $this->verifyUserExist($data);
            if(is_array($user_exist)){
                return $this->render('pages/activations.html.twig',
                    ["user_id"=>$user_exist["error_content"]["user_id"],
                        "user_email"=>$user_exist["error_content"]["email"]
                    ]);

            }
           if (is_int($user_exist) && $user_exist==404){
               $session=$this->get("session");
               $session->set("phone_number",$data->sender_phone);
               $session->set("country_code",$data->sender_country_code);

               return new RedirectResponse($this->urlGenerator->generate('register'));
           }


        $params=$data;
        $endpoint="mobilemoney/".$data->id."/new";
        $headers=[
            'Accept' => 'application/json',
            "apikey"=> $this->apikey
        ];

        $response=$this->make_post_request((array)$params,$headers,$endpoint,(array)$data);
        $response=$this->handle_response($response,"array");

        /*
         * si la transaction a été initiée ou echec de l'initiation l'efacer de la session
         */
        $session = $this->get('session');
        $session->remove('transaction');

        /*
        * si les datas proviennent du formulaire renvoyer json sinon renvoyer la page operations
         * */
        if(!isset($data->local)){
            $data=(array)$data;
            $data["transaction_status"]=$response;
            //echo ("++++++++++++++++++++");
            //var_dump($data);
            return $this->render('pages/operationQs.html.twig',
                ["transaction"=>(array)$data,"transaction_status"=>$response]);

        }
        return $response;
    }

    public function updateAction(Request $request,$id)
    {
        $this->apikey = $this->getParameter('app.apikey');
        $this->site_url = $this->getParameter('app.site_url');
        /*
         * $data c'est le mot de passe
         */
        $data = $request->getContent();

        $data=json_decode($data);

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
        $login["sender_phone"]=$transaction->sender_phone;
        $login["sender_country_code"]=$transaction->sender_country_code;
        $login["password"]=$data->password;
        $user_exist=$this->verifyUserExist($login);
            if(is_array($user_exist)){
                return $this->render('pages/activations.html.twig',
                    ["user_id"=>$user_exist["error_content"]["user_id"],
                        "user_email"=>$user_exist["error_content"]["email"]]);

            }

        /*
         * si le type de retour est un entier alors c'est à dire un utilisateur alors erreur
         *
         */
        if( is_int($user_exist) ){

                $res =[
                    "code"=>(string)$user_exist,
                    "message" => 'erreur Authentification'
                ];

            return new Response(json_encode($res), Response::HTTP_OK,[
                "Content-Type"=>'application/json'
            ]);
        }

        /*
         * ajout des paramètres necessaires pour la requete
         */
        $data["status"]="confirmed";
        $data["response"]="";
        $data["step"]="";

        $response=$this->make_put_request((array)$data,$headers,$endpoint,(array)$data);
        $response=$this->handle_response($response,false);
        return $response;
    }

    public function start()
    {
        return $this->render('pages/operation.html.twig');
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
                            "id"=>12,
                            "status"=>"initiated",
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
     * if $resp=="array" retourne array sinon retourne json
     *
     */
    private function handle_response($response,$resp)
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
        if (!$resp=="array"){
            return new Response(json_encode($response), Response::HTTP_CREATED,[
                "Content-Type"=>'application/json'
            ]);
        }
        return $res;
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

            if($exception->getCode()==404){
                $this->addFlash(
                    'notice',
                    'in order to do this operation you must create Create a daticash account.'
                );
                return 404;
            }
            if($exception->getCode()==412 ){
                $this->addFlash(
                    'notice',
                    'in order to do this operation you must Activate your account.'
                );
                $response=[
                    "code"=>$exception->getCode(),
                    "error_content"=>$exception->getMessageData()
                ];
                return $response;
            }
            if($exception->getCode()==401){
                return 401;
            }
            if (!($exception->getCode()==401 || $exception->getCode()==412 || $exception->getCode()==404)){
                return $exception->getCode();
            }
        }

    }
}
