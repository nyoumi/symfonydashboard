<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\Mime\Part\DataPart;
use Symfony\Component\Mime\Part\Multipart\FormDataPart;
/**
 * Class ActivationController
 * @package App\Controller
 */
class ProfileController extends AbstractController
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
    private $security;

    /**
     * ActivationController constructor.
     * @param UrlGeneratorInterface $urlGenerator
     * @param HttpClientInterface $client
     */
    public function __construct(UrlGeneratorInterface $urlGenerator,HttpClientInterface $client,Security $security)
    {
        $this->client = $client;
        $this->urlGenerator = $urlGenerator;
        $this->security=$security;



    }

    /**
     * @param Request $request
     * @param $id
     * @return RedirectResponse|Response
     */
    public function editAction(Request $request,$id)
    {


        if($request->getMethod()=="POST"){
            // }
            // $file = $request->files->get('avatar');

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
            var_dump($data);
            $endpoint="user/".$id."/edit";
            $headers=[
                'Accept' => 'application/json',
                "apikey"=> $this->apikey,
                'Content-Type' => "multipart/form-data"
            ];

            $params=(array)$data;

            $response=$this->make_put_request($params,$headers,$endpoint,(array)$data);
        }


        return $this->render('pages/settings.html.twig',[
            ]
        );



       // return new RedirectResponse($this->urlGenerator->generate('home'));



    }

    /**
     * @param Request $request
     * @param $id
     * @return Response
     */
    public function uploadAction(Request $request,$id)
    {


        if($request->getMethod()=="POST"){
            $data = $request->getContent();
            var_dump($data);

            $data=json_decode($data);


            if(!is_object($data)){
                parse_str($request->getContent(),$output);
                $data=(object) $output;

            }

            if($this->emptyObj($data) ){
                $data=$request->query->all();
                $data=(object) $data;

            }
             $file = $request->files->get('avatar');
            var_dump($file);
            $user = $this->security->getUser();

            $endpoint="user/".$id."/edit";
            $headers=[
                'Accept' => 'application/json',
                "apikey"=> $this->apikey,
                'Content-Type' => "multipart/form-data"
            ];

            $params=[];
            // var_dump($data);
            $response=$this->make_put_request($params,$headers,$endpoint,$data);
            return new Response(json_encode($response), Response::HTTP_OK,[
                "Content-Type"=>'application/json'
            ]);
        }

        $headers=[
            'Accept' => 'application/json',
            "apikey"=> $this->apikey
        ];
        $endpoint="user/".$id;
        $params=[];
        try {
            $user = $this->make_get_request($params,$headers,$endpoint);

        } catch (\Throwable $th) {
            //throw $th;
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
                    'body' => $body


                ]
            );
            var_dump($response->getStatusCode());
            var_dump($response->getContent(false));


            if($response->getStatusCode()==201 | $response->getStatusCode()==200){

                return $response->toArray();
            }else {


                return [
                    "code"=>$response->getStatusCode()
                ];
            }




        } catch (TransportExceptionInterface | ClientExceptionInterface |ServerExceptionInterface| DecodingExceptionInterface | RedirectionExceptionInterface $e) {

            $this->addFlash(
                'error',
                'An internal error has occurred please try again later!'
            );
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

            $this->addFlash(
                'error',
                'An internal error has occurred please try again later!'
            );
            return [
                "code"=>0
            ];
        }



    }
}
