<?php

namespace App\Controller;

use App\Entity\MyLogger;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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

/**
 * Class ActivationController
 * @package App\Controller
 */
class ServiceController extends AbstractController
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
     * @param Security $security
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
    public function viewAction(Request $request)
    {
        $endpoint="accounts/types";
        $headers=[
            'Accept' => 'application/json',
            "apikey"=> $this->apikey
        ];

        $params=[];
        $account_types=[];
        $response=$this->make_get_request($params,$headers,$endpoint);
        if(isset($response['code'])){
            $account_types["account_types"]=$response;
        }


        return $this->render('pages/service_manager.html.twig',[
                "account_types"=>$response
            ]
        );

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


            return [
                "code"=>0
            ];
        }



    }


    public function make_post_request(array $parameters, array $header, $endpoint, $body)
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


            try{
                file_put_contents(__DIR__."/../../var/log/request".date("j.n.Y").'.log',(string) $response->toArray(true), FILE_APPEND);

                MyLogger::writeRequestLog($response->toArray());

            }catch (Exception $e){
                // $log=$e->getTraceAsString();
                // file_put_contents(__DIR__."/../../var/log/logger_".date("j.n.Y").'.log', $log, FILE_APPEND);

            }
            if ($response->getStatusCode() == 200 || $response->getStatusCode() == 201 || $response->getStatusCode() == 202) {
                return $response->toArray();
            } else {
                return $response->getStatusCode();
            }
        } catch (TransportExceptionInterface |DecodingExceptionInterface
        |ClientExceptionInterface|RedirectionExceptionInterface|ServerExceptionInterface $e) {


            return null;
        }
    }


    private function handle_response($response, $resp)
    {
        if (isset($response) && is_int($response)) {
            $res = [
                "code" => (string)$response
            ];
        }
        if (isset($response) && is_array($response)) {
            $res = $response;
        }
        if (!isset($response)) {
            $res = [
                "code" => (string)0,
                "message" => 'erreur interne au serveur'
            ];
        }
        /*
         * si la fonction demande un retour en array ou en reponse http
         */
        if (!($resp == "array")) {
            return new Response(json_encode($res), Response::HTTP_CREATED, [
                "Content-Type" => 'application/json'
            ]);
        }
        return $res;
    }

    private function emptyObj( $obj ) {
        foreach ( $obj AS $prop ) {
            array($prop);
            return FALSE;
        }

        return TRUE;
    }
}
