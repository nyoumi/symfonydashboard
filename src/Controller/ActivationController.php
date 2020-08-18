<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Class ActivationController
 * @package App\Controller
 */
class ActivationController extends AbstractController
{
    /**
     * @var HttpClientInterface
     */
    private $client;
    /**
     * @var
     */
    private $site_url ;
    /**
     * @var
     */
    private $apikey ;

    /**
     * ActivationController constructor.
     * @param HttpClientInterface $client
     */
    public function __construct(HttpClientInterface $client)
    {
        $this->client = $client;

    }

    /**
     * @param $id
     * @param $token
     * @return Response
     */
    public function activate($id, $token)
    {

        $endpoint="user/".$id."/activate";
        $headers=[
            'Accept' => 'application/json',
            "apikey"=> $this->apikey
        ];

        $params=[
            'token' => $token,
            'id' =>  $id,
        ];
        $response=$this->make_put_request($params,$headers,$endpoint,$params);




        return new Response(json_encode($response), Response::HTTP_OK,[
            "Content-Type"=>'application/json'
        ]);
    }

    public function activatedAction(Request $request)
    {


        $activated=$request->query->get("activated");
        if ("true"==$activated){
            $this->addFlash(
                'activated',
                'Compte activé'
            );
        }else{
            $this->addFlash(
                'inapropriate',
                'merci de votre confiance.'
            );
        }
        return $this->render('pages/action.html.twig',
            ["activated"=>$activated
            ]);
    }

    public function activation($user_id,$email)
    {
        return $this->render('pages/activations.html.twig',
            ["user_id"=>$user_id,
                "user_email"=>$email
            ]);
    }

    /**
     * @param $type
     * @param $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function send_activation_token($type,$id)
    {
        if($type=="sms"){
            $endpoint="users/".$id."/token-sms";

        }else{
            $endpoint="users/".$id."/token-mail";

        }
        $headers=[
            'Accept' => 'application/json',
            "apikey"=> $this->apikey
        ];

        $params=[
            'id' =>  $id,
        ];
        $response=$this->make_get_request($params,$headers,$endpoint);
        return new Response(json_encode($response), Response::HTTP_OK,[
            "Content-Type"=>'application/json'
        ]);
    }

    public function verify(Request $request)
    {
        /*
         * lecture des données envoyées par post, submit ou dans la requête
         */

        $data = $request->getContent();
        $data=json_decode($data);


        if(!is_object($data)){
            parse_str($request->getContent(),$output);
            $data=(object) $output;
        }

        if($this->emptyObj($data) ){
            $data=$request->query->all();
            $data=(object) $data;
        }
        $endpoint="user/".$data->id."/verify-contact";
        $headers=[
            'Accept' => 'application/json',
            "apikey"=> $this->apikey
        ];

        $params=(array)$data;

        $response=$this->make_put_request($params,$headers,$endpoint,(array)$data);
        return new Response(json_encode($response), Response::HTTP_OK,[
            "Content-Type"=>'application/json'
        ]);

    }

    /**
     * @param array $parameters
     * @param array $header
     * @param $endpoint
     * @return null
     */
    public function make_put_request(array $parameters, array $header, $endpoint,$body)
    {

        $this->site_url = $this->getParameter('app.site_url');
        $this->apikey = $this->getParameter('app.apikey');
        $header["apikey"]=$this->apikey;

        $params = "";
        foreach ($parameters as $key => $value) {
            $params = $params . $key . "=" . $value . "&";

        }
        $params = substr($params, 0, -1);
        //$params = *

        try {
            $response = $this->client->request(
                'PUT',
                $this->site_url . $endpoint . "?" . $params,
                [
                    'headers' => $header,
                    'json' => $body


                ]
            );

            if($response->getStatusCode()==200 |$response->getStatusCode()==208|$response->getStatusCode()==201 ){

                return $response->toArray();
            }else {

                return [
                    "code"=>$response->getStatusCode()
                ];
            }

        } catch (TransportExceptionInterface | ClientExceptionInterface |ServerExceptionInterface| DecodingExceptionInterface | RedirectionExceptionInterface $e) {


            return [
                "code"=>0
            ];
        }



    }
    /**
     * @param array $parameters
     * @param array $header
     * @param $endpoint
     * @return null
     */
    public function make_get_request(array $parameters, array $header, $endpoint)
    {

        $this->site_url = $this->getParameter('app.site_url');
        $this->apikey = $this->getParameter('app.apikey');
        $header["apikey"]=$this->apikey;

        $params = "";
        foreach ($parameters as $key => $value) {
            $params = $params . $key . "=" . $value . "&";

        }
        $params = substr($params, 0, -1);
        //$params = *

        try {
            $response = $this->client->request(
                'GET',
                $this->site_url . $endpoint . "?" . $params,
                [
                    'headers' => $header,

                ]
            );

            if($response->getStatusCode()==201 | $response->getStatusCode()==200
                | $response->getStatusCode()==202 | $response->getStatusCode()==208){
                $code=$response->getStatusCode();
                $response=$response->toArray();

                $response["code"]=$code;

                return $response;
            }else {


                return [
                    "code"=>$response->getStatusCode()
                ];
            }




        } catch (TransportExceptionInterface | ClientExceptionInterface |ServerExceptionInterface| DecodingExceptionInterface | RedirectionExceptionInterface $e) {

            return [
                "code"=>0
            ];
        }



    }

    private function emptyObj( $obj ) {
        foreach ( $obj AS $prop ) {
            array($prop);
            return FALSE;
        }

        return TRUE;
    }

}
