<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
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
class PasswordController extends AbstractController
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
    private $urlGenerator;


    /**
     * ActivationController constructor.
     * @param HttpClientInterface $client
     */
    public function __construct(HttpClientInterface $client,UrlGeneratorInterface $urlGenerator)
    {
        $this->client = $client;
        $this->urlGenerator = $urlGenerator;

    }

    /**
     * @param Request $request
     * @return Response
     */
    public function requestReset(Request $request)
    {
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
        $endpoint="user/request-reset-password";
        $headers=[
            'Accept' => 'application/json',
            "apikey"=> $this->apikey
        ];

        $params=(array)$data;
        $response=$this->make_get_request($params,$headers,$endpoint);




        return new Response(json_encode($response), Response::HTTP_OK,[
            "Content-Type"=>'application/json'
        ]);
    }
    /**
     * @param Request $request
     */
    public function requestTrue(Request $request,$operation)
    {
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
        if($request->getMethod()=="POST"){
            var_dump($data);
            if($operation == "reset"){
                $endpoint="user/".$data->user_id."/reset-apply-new-password/".$data->token;
                $headers=[
                    'Accept' => 'application/json',
                    "apikey"=> $this->apikey
                ];

                $params=(array)$data;
                $response=$this->make_post_request($params,$headers,$endpoint,$params);

                return new Response(json_encode($response), Response::HTTP_OK,[
                    "Content-Type"=>'application/json'
                ]);

            }
            if($operation == "change"){
                $endpoint="user/".$data->user_id."/change-password";
                $headers=[
                    'Accept' => 'application/json',
                    "apikey"=> $this->apikey
                ];

                $params=(array)$data;
                $response=$this->make_post_request($params,$headers,$endpoint,$params);

                return new Response(json_encode($response), Response::HTTP_OK,[
                    "Content-Type"=>'application/json'
                ]);

            }

        }elseif (isset($data->reset)&& $data->reset=="true"){
            var_dump($data);
            $this->addFlash(
                'reset',
                'reset accepted'
            );
            return $this->render('pages/request.html.twig',
                ["user_id"=>$data->user_id,
                    "token"=>$data->token,
                    "reset"=>$data->reset
                ]);
        }
        elseif (isset($data->change) && $data->change=="true"){
            $this->addFlash(
                'change',
                'change accepted'
            );
            return $this->render('pages/request.html.twig',
                ["user_id"=>$data->user_id,
                    "token"=>"",

                ]);
        }else{
            return new RedirectResponse($this->urlGenerator->generate('home'));

        }

    }
    private function emptyObj( $obj ) {
        foreach ( $obj AS $prop ) {
            return FALSE;
        }

        return TRUE;
    }

    public function activatedAction(Request $request)
    {


        $activated=$request->query->get("activated");
        if (true==$activated){
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
        return $this->render('pages/activations.html.twig',
            ["activated"=>$activated
            ]);
    }

    /**
     * @param $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function send_sms($id)
    {
        $endpoint="user/".$id."/send_sms";
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

    /**
     * @param array $parameters
     * @param array $header
     * @param $endpoint
     * @param $body
     * @return null
     */
    public function make_post_request(array $parameters, array $header, $endpoint,$body)
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
                'POST',
                $this->site_url . $endpoint . "?" . $params,
                [
                    'headers' => $header,
                    'json' => $body


                ]
            );

            if($response->getStatusCode()==201 | $response->getStatusCode()==200){

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

            if($response->getStatusCode()==201 | $response->getStatusCode()==200){

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
}
