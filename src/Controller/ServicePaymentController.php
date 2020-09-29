<?php

namespace App\Controller;

use App\Entity\DatiTransaction;
use App\Entity\MyLogger;
use Exception;
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
class ServicePaymentController extends AbstractController
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
    private $create_action="create";

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
    public function viewAction(Request $request,$service_id)
    {
        $format=$request->get("format");

        $endpoint="payment-service/".$service_id."/view";
        $headers=[
            'Accept' => 'application/json',
            "apikey"=> $this->apikey
        ];

        $params=[$service_id];
        $service=[];
        $service=["id"=>4,"cost"=>1000000,"currency"=>"XAF","label"=>"erreur de service","description"=>"nous n'avons paas pu trouver le service recherché","logo"=>""];
        $response=$this->make_get_request($params,$headers,$endpoint);
        if(isset($response) && isset($response['id'])){
            $service=$response;
        }

        if(isset($format) && strcmp($format, "json") == 0){
            return new Response(json_encode($service), Response::HTTP_OK,[
                "Content-Type"=>'application/json'
            ]);
        }
        return $this->render('pages/services/pages/service.html.twig',[
                "service"=>$service
            ]
        );
        // return new RedirectResponse($this->urlGenerator->generate('home'));

    }
    public function listAction(Request $request)
    {

        $endpoint="payment-services/list";
        $headers=[
            'Accept' => 'application/json',
            "apikey"=> $this->apikey
        ];

        $params=[];
        //$service=["id"=>4,"label"=>"erreur de service","description"=>"nous n'avons pas pu trouver le service recherché","logo"=>""];
        $response=$this->make_get_request($params,$headers,$endpoint);

            return new Response(json_encode($response), Response::HTTP_OK,[
                "Content-Type"=>'application/json'
            ]);


        // return new RedirectResponse($this->urlGenerator->generate('home'));

    }


    /**
     * @param $merchant_id
     * @return Response
     */
    public function merchantListAction($merchant_id)
    {

        $endpoint="payment-services/merchant/".$merchant_id."/list";
        $headers=[
            'Accept' => 'application/json',
            "apikey"=> $this->apikey
        ];

        $params=[];
        //$service=["id"=>4,"label"=>"erreur de service","description"=>"nous n'avons pas pu trouver le service recherché","logo"=>""];
        $response=$this->make_get_request($params,$headers,$endpoint);

        return new Response(json_encode($response), Response::HTTP_OK,[
            "Content-Type"=>'application/json'
        ]);



    }



    public function participantCheckAction(Request $request, $service_id)
    {

      $endpoint="payment-services/".$service_id."/participant-check";
        $headers=[
            'Accept' => 'application/json',
            "apikey"=> $this->apikey
        ];
        $data = $request->getContent();
        $data = json_decode($data);

        if (!is_object($data)) {
            parse_str($request->getContent(), $output);
            $data = (object)$output;
        }

        if ($this->emptyObj($data)) {
            $data = $request->query->all();
            $data = (object)$data;
        }


        $params=(array)$data;

        $response=$this->make_get_request($params,$headers,$endpoint);

        return new Response(json_encode($response), Response::HTTP_OK,[
            "Content-Type"=>'application/json'
        ]);

    }
    public function participantLoginAction(Request $request, $service_id)
    {
        $endpoint="payment-service/partipant-authenticate";
        $headers=[
            'Accept' => 'application/json',
            "apikey"=> $this->apikey
        ];
        $data = $request->getContent();
        $data = json_decode($data);

        if (!is_object($data)) {
            parse_str($request->getContent(), $output);
            $data = (object)$output;
        }

        if ($this->emptyObj($data)) {
            $data = $request->query->all();
            $data = (object)$data;
        }


        $params=(array)$data;

        $response=$this->make_get_request($params,$headers,$endpoint);

        return new Response(json_encode($response), Response::HTTP_OK,[
            "Content-Type"=>'application/json'
        ]);

    }

    public function participantListAction(Request $request)
    {

        $endpoint="payment-services/participant/list";
        $headers=[
            'Accept' => 'application/json',
            "apikey"=> $this->apikey
        ];
        $data = $request->getContent();
        $data = json_decode($data);

        if (!is_object($data)) {
            parse_str($request->getContent(), $output);
            $data = (object)$output;
        }

        if ($this->emptyObj($data)) {
            $data = $request->query->all();
            $data = (object)$data;
        }


        $params=(array)$data;
        //$service=["id"=>4,"label"=>"erreur de service","description"=>"nous n'avons pas pu trouver le service recherché","logo"=>""];
        $response=$this->make_get_request($params,$headers,$endpoint);

        return new Response(json_encode($response), Response::HTTP_OK,[
            "Content-Type"=>'application/json'
        ]);



    }


    public function getHistoryAction(Request $request)
    {

            $endpoint="payment-services/participant-history-list";
            $headers=[
                'Accept' => 'application/json',
                "apikey"=> $this->apikey
            ];
            $data = $request->getContent();
            $data = json_decode($data);

            if (!is_object($data)) {
                parse_str($request->getContent(), $output);
                $data = (object)$output;
            }

            if ($this->emptyObj($data)) {
                $data = $request->query->all();
                $data = (object)$data;
            }


            $params=(array)$data;
            //$service=["id"=>4,"label"=>"erreur de service","description"=>"nous n'avons pas pu trouver le service recherché","logo"=>""];
            $response=$this->make_get_request($params,$headers,$endpoint);

            return new Response(json_encode($response), Response::HTTP_OK,[
                "Content-Type"=>'application/json'
            ]);


        }

    public function payAction(Request $request,$service_id)
    {
        $endpoint="payment-service/".$service_id."/pay";
        $headers=[
            'Accept' => 'application/json',
            "apikey"=> $this->apikey
        ];
        $data = $request->getContent();
        $data = json_decode($data);

        if (!is_object($data)) {
            parse_str($request->getContent(), $output);
            $data = (object)$output;
        }

        if ($this->emptyObj($data)) {
            $data = $request->query->all();
            $data = (object)$data;
        }



        $params=(array)$data;
        //$service=["id"=>4,"label"=>"erreur de service","description"=>"nous n'avons pas pu trouver le service recherché","logo"=>""];
        $response=$this->make_post_request($params,$headers,$endpoint,$params);
        $responseToReturn = $this->handle_response($response, false);
        $response = $this->handle_response($response, "array");
        if (isset($response["id"])) {
            $this->createLocalAction($response,(array) $data,$service_id);
        }
        return $responseToReturn;
    }


    public function createAction(Request $request,$merchant_id)
    {
        $endpoint="payment-services/".$merchant_id."/new";
        $headers=[
            'Accept' => 'application/json',
            "apikey"=> $this->apikey
        ];
        $data = $request->getContent();
        $data = json_decode($data);

        if (!is_object($data)) {
            parse_str($request->getContent(), $output);
            $data = (object)$output;
        }

        if ($this->emptyObj($data)) {
            $data = $request->query->all();
            $data = (object)$data;
        }


        $params=(array)$data;
        //$service=["id"=>4,"label"=>"erreur de service","description"=>"nous n'avons pas pu trouver le service recherché","logo"=>""];
        $response=$this->make_post_request($params,$headers,$endpoint,$params);
        $response = $this->handle_response($response, false);
        return $response;




    }
    public function participantAddAction(Request $request, $service_id)
    {
        $endpoint="payment-service/".$service_id."/add-participants";
        $headers=[
            'Accept' => 'application/json',
            "apikey"=> $this->apikey
        ];
        $data = $request->getContent();
        $data = json_decode($data);

        if (!is_object($data)) {
            parse_str($request->getContent(), $output);
            $data = (object)$output;
        }

        if ($this->emptyObj($data)) {
            $data = $request->query->all();
            $data = (object)$data;
        }


        $params=(array)$data;
        $body=json_encode((array)$data);
        $body_data=[];
        array_push($body_data,$data);
        $body_data=json_encode($body_data);



        //$service=["id"=>13,"label"=>"erreur de service","description"=>"nous n'avons pas pu trouver le service recherché","logo"=>""];
        $response=$this->make_put_request($params,$headers,$endpoint,$body_data);
        $response = $this->handle_response($response, false);

        return $response;
    }
    public function participantEnableAction(Request $request, $service_id,$participant_id)
    {
        $endpoint="payment-service/".$service_id."/participant/".$participant_id."/enable";
        $headers=[
            'Accept' => 'application/json',
            "apikey"=> $this->apikey
        ];
        $data = $request->getContent();
        $data = json_decode($data);

        if (!is_object($data)) {
            parse_str($request->getContent(), $output);
            $data = (object)$output;
        }

        if ($this->emptyObj($data)) {
            $data = $request->query->all();
            $data = (object)$data;
        }


        $params=(array)$data;

        $response=$this->make_put_request($params,$headers,$endpoint,$params);
        $response = $this->handle_response($response, false);

        return $response;
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

            if($response->getStatusCode()==201 | $response->getStatusCode()==200 | $response->getStatusCode()==202){

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






    private function createRandom($len)
    {
        $arr = str_split('152763736365372636373626209989092887267152635344710298736'); // get all the characters into an array
        shuffle($arr); // randomize the array
        $arr = array_slice($arr, 0, $len); // get the first six (random) characters out
        $str = implode('', $arr);
        return $str;
    }

    public function createLocalAction($response, $transactionData,$service_id)
    {
        $entityManager = $this->getDoctrine()->getManager();

        $transaction = $this->getDoctrine()
            ->getRepository(DatiTransaction::class)
            ->find($response["id"]);

        if (!$transaction) {

            $transaction = new DatiTransaction();

        }


        $transaction->setId($response["id"]);
        $transaction->setStatus($response["status"]);
        if(isset($response["step_description"])) $transaction->setStepDescription($response["step_description"]);


        $transaction->setType($service_id);
        $transaction->setAmountSent($transactionData["amount"]);
        $transaction->setCurrencySent($transactionData["currency"]);
        $transaction->setSenderPhone($transactionData["mobile_money_account"]);
        $transaction->setSenderCountryCode($transactionData["country_code"]);
        $transaction->setRecipientCountryCode($transactionData["country_code"]);
        $transaction->setRecipientPhone($transactionData["phone_number"]);
        $transaction->setCreatedAt($response["created_at"]);

        $entityManager->persist($transaction);

        // actually executes the queries (i.e. the INSERT query)
        $entityManager->flush();
        // $transaction = $this->serializer->deserialize($transaction, 'json');
        //$transaction = $this->serializer->serialize($transaction, 'json');

        return $transaction;

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

        $this->site_url = $this->getParameter('app.site_url');
        $this->apikey = $this->getParameter('app.apikey');
        $header["apikey"]=$this->apikey;
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
            var_dump($e);

            return null;
        }
    }


    private function handle_response($response, $response_type)
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
        if (!($response_type == "array")) {
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


    private function saveToSession($text,$transaction)
    {
        $this->get('session')->set($text,$transaction);

    }
    private function removeToSession($key){
        $this->get('session')->remove($key);
    }
}
