<?php

namespace App\Controller;

use App\Entity\DatiTransaction;
use App\Entity\MyLogger;
use DateTime;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Security\User\WebserviceUser;
use JMS\Serializer\SerializerInterface;

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
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

use Psr\Log\LoggerInterface;


class DatiTransactionController extends AbstractController
{
    private $client;
    private $site_url;
    private $apikey;
    private $serializer;
    private $security;
    private $userProvider;
    private $urlGenerator;
    private $req=0;

    public function __construct(HttpClientInterface $client,
                                UserProviderInterface $userProvider, UrlGeneratorInterface $urlGenerator,
                                SerializerInterface $serialize, Security $security)
    {
        $this->client = $client;
        //$this->serializer=$serialize;
        $this->security = $security;
        $this->userProvider = $userProvider;
        $this->urlGenerator = $urlGenerator;

        $encoders = [new JsonEncoder()];
        $normalizers = [new ObjectNormalizer()];

        $this->serializer = new Serializer($normalizers, $encoders);
    }

    public function view($id,LoggerInterface $logger)
    {
        $this->apikey = $this->getParameter('app.apikey');
        $this->site_url = $this->getParameter('app.site_url');
        $params = [];
        $endpoint = "mobilemoney/" . $id;
        $headers = [
            'Accept' => 'application/json',
            "apikey" => $this->apikey
        ];

        //$response = $this->make_get_request((array)$params, $headers, $endpoint);
        //$response = $this->handle_response($response, false);
        //return $response;
        return $this->viewLocal($id,$logger);

    }

    public function viewLocal($id,LoggerInterface $logger)
    {

        //$transaction=new DatiTransaction();

        //$transaction->setStatus()
        $transaction = $this->getDoctrine()
            ->getRepository(DatiTransaction::class)
            ->find($id);

        if (!$transaction || $transaction==null) {

            return new Response(json_encode([
                'code' => '400',
                "message" => "entity not found"
            ]), Response::HTTP_NOT_FOUND, [
                "Content-Type" => 'application/json'
            ]);
        }



        $logger->info('found transaction----------------->',(array)$transaction);
        MyLogger::writeLog((array)$transaction);

        return new Response($this->serializer->serialize($transaction,'json')
            //serialize($transaction)
            , Response::HTTP_OK
            , [
                "Content-Type" => 'application/json'
            ]);
    }


    public function createAction(Request $request)
    {

        $this->apikey = $this->getParameter('app.apikey');
        $this->site_url = $this->getParameter('app.site_url');

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
        //var_dump($data);
        if ($this->emptyObj($data)) {
            $session = $this->get('session');
            $data = $session->get("transaction_initiated");
        }

        $this->saveToSession($data);
        /*        if (!isset($data->local)) {
                    $user_exist = $this->verifyUserExist((array)$data);

                    if (is_array($user_exist)) {
                        return $this->render('pages/activations.html.twig',
                            ["user_id" => $user_exist["error_content"]["user_id"],
                                "user_email" => $user_exist["error_content"]["email"]
                            ]);

                    }
                    if (is_int($user_exist) && $user_exist == 404) {
                        $session = $this->get("session");
                        $session->set("phone_number", $data->sender_phone);
                        $session->set("country_code", $data->sender_country_code);

                        return new RedirectResponse($this->urlGenerator->generate('register'));
                    }
                }*/


        $params = $data;
        /*
         * @TODO à effacer à cause de l'id qui est fixe
         */
        //$endpoint = "mobilemoney/" . $data->id . "/new";
        $endpoint = "mobilemoney/" . 2 . "/new";
        $headers = [
            'Accept' => 'application/json',
            "apikey" => $this->apikey
        ];

        $response = $this->make_post_request((array)$params, $headers, $endpoint, (array)$data);
        $response = $this->handle_response($response, "array");

        /*
         * @TODO à effacer

              $res = '{"id": 12,"status": "CREATED","created_at": ""}';
              $res = (array) json_decode($res);
             // $res["id"] = $this->createRandom(4);
              $date= new DateTime('NOW');
              $res["created_at"]= $date->format('Y-m-d H:i:s');

              $response = $res;
        */
        if (isset($response["id"])) {
            $this->createLocalAction($response,(array) $data);
        }

        /*
         * si la transaction a été initiée ou echec de l'initiation l'effacer de la session
         */
        // $session = $this->get('session');
        //$session->remove('transaction_initiated');
        /*
        * si les datas proviennent du formulaire externe renvoyer json sinon renvoyer la page operations
         * */
        if (!isset($data->local)) {
            $data = (array)$data;
            $data["transaction_status"] = $response;
            //echo ("++++++++++++++++++++");
            return $this->render('pages/operationQS.html.twig',
                ["transaction" => (array)$data, "transaction_status" => $response]);
        }
        return new Response(json_encode($response), Response::HTTP_CREATED, [
            "Content-Type" => 'application/json'
        ]);
    }

    private function createRandom($len)
    {
        $arr = str_split('152763736365372636373626209989092887267152635344710298736'); // get all the characters into an array
        shuffle($arr); // randomize the array
        $arr = array_slice($arr, 0, $len); // get the first six (random) characters out
        $str = implode('', $arr);
        return $str;
    }

    public function createLocalAction($response, $transactionData)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $transaction = new DatiTransaction();

        $transaction->setId($response["id"]);

        $transaction->setType($transactionData["id"]);
        $transaction->setAmountSent($transactionData["amount_sent"]);
        $transaction->setCurrencySent($transactionData["currency_sent"]);
        $transaction->setSenderPhone($transactionData["sender_phone"]);
        $transaction->setSenderCountryCode($transactionData["sender_country_code"]);
        $transaction->setRecipientCountryCode($transactionData["recipient_country_code"]);
        $transaction->setRecipientPhone($transactionData["recipient_phone"]);
        $transaction->setCreatedAt($response["created_at"]);

        /*
         * @TODO à effacer

        $tran = $this->getDoctrine()
            ->getRepository(DatiTransaction::class)
            ->find($response["id"]);
        if(isset($tran)){
            $entityManager->remove($tran);
            $entityManager->flush();
        }*/

        // tell Doctrine you want to (eventually) save the Product (no queries yet)
        $entityManager->persist($transaction);

        // actually executes the queries (i.e. the INSERT query)
        $entityManager->flush();
        // $transaction = $this->serializer->deserialize($transaction, 'json');
        //$transaction = $this->serializer->serialize($transaction, 'json');

        return $transaction;

    }

    private function emptyObj($obj)
    {
        foreach ($obj AS $prop) {
            return FALSE;
        }

        return TRUE;
    }

    public function getPricing(Request $request)
    {
        $this->apikey = $this->getParameter('app.apikey');
        $this->site_url = $this->getParameter('app.site_url');

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
        $endpoint = "mobilemoney/pricing";
        $headers = [
            'Accept' => 'application/json',
            "apikey" => $this->apikey
        ];

        $params = (array)$data;
        $response = $this->make_get_request($params, $headers, $endpoint);

        return new Response(json_encode($response), Response::HTTP_OK, [
            "Content-Type" => 'application/json'
        ]);

    }

    public function convert(Request $request)
    {
        $this->apikey = $this->getParameter('app.apikey');
        $this->site_url = $this->getParameter('app.site_url');

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
        $endpoint = "mobilemoney/convert";
        $headers = [
            'Accept' => 'application/json',
            "apikey" => $this->apikey
        ];

        $params = (array)$data;
        $response = $this->make_get_request($params, $headers, $endpoint);

        return new Response(json_encode($response), Response::HTTP_OK, [
            "Content-Type" => 'application/json'
        ]);

    }

    public function updateAction(Request $request, $id)
    {

        /*
         * @TODO à effacer

        return new Response(json_encode([
            'code' => '201',
            "message" => "created"
        ]), Response::HTTP_CREATED, [
            "Content-Type" => 'application/json'
        ]);*/

        $this->apikey = $this->getParameter('app.apikey');
        $this->site_url = $this->getParameter('app.site_url');
        /*
         * $data c'est le mot de passe
         */
        $data = $request->getContent();

        $data = json_decode($data);

        $endpoint = "mobilemoney/" . $id;
        $headers = [
            'Accept' => 'application/json',
            "apikey" => $this->apikey
        ];


        /*
         * recupère la transaction dans la session afin de recupérer le country code et le phone_number
         * de l'utilisateur ensuite essaye de le connecter un utilisant le mot de passe fourni
         */
        $session = $this->get('session');
        $transaction = $session->get('transaction');
        $login = [];
        $login["sender_phone"] = $transaction->sender_phone;
        $login["sender_country_code"] = $transaction->sender_country_code;
        $login["password"] = $data->password;
        $user_exist = $this->verifyUserExist($login);
        if (is_array($user_exist)) {
            return $this->render('pages/activations.html.twig',
                ["user_id" => $user_exist["error_content"]["user_id"],
                    "user_email" => $user_exist["error_content"]["email"]]);

        }

        /*
         * si le type de retour est un entier alors c'est à dire un utilisateur alors erreur
         *
         */
        if (is_int($user_exist)) {

            $res = [
                "code" => (string)$user_exist,
                "message" => 'erreur Authentification'
            ];

            return new Response(json_encode($res), Response::HTTP_OK, [
                "Content-Type" => 'application/json'
            ]);
        }

        /*
         * ajout des paramètres necessaires pour la requete
         */
        $data["status"] = "confirmed";
        $data["response"] = "";
        $data["step"] = "";

        $response = $this->make_put_request((array)$data, $headers, $endpoint, (array)$data);
        $response = $this->handle_response($response, false);
        return $response;
    }

    public function updateActionWebhookAction()
    {
        return new Response(json_encode(['code' => 200]), Response::HTTP_OK, [
            "Content-Type" => 'application/json'
        ]);
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

            if ($response->getStatusCode() == 200 || $response->getStatusCode() == 201) {

                return $response->toArray();
            } else {
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

    public function make_put_request(array $parameters, array $header, $endpoint, $body)
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
            if ($response->getStatusCode() == 200 || $response->getStatusCode() == 201 || $response->getStatusCode() == 202) {
                return $response->toArray();
            } else {
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


            if ($response->getStatusCode() == 200 || $response->getStatusCode() == 201 || $response->getStatusCode() == 202) {
                return $response->toArray();
            } else {
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
        if (!$resp == "array") {
            return new Response(json_encode($response), Response::HTTP_CREATED, [
                "Content-Type" => 'application/json'
            ]);
        }
        return $res;
    }

    private function saveToSession($transaction)
    {
        $this->get('session')->set('transaction_initiated', $transaction);

    }

    /*
     * vérifie que l'utilisateur existe et le redirige
     */
    private function verifyUserExist($data)
    {
        $login = new WebserviceUser();
        $data = (array)$data;
        $login->setCountryCode($data["sender_country_code"]);
        $login->setPhoneNumber($data["sender_phone"]);
        if (isset($data["password"])) {
            $login->setPassword($data["password"]);

        } else {
            $login->setPassword("11");
        }
        try {
            $user = $this->userProvider->loadUserByUsername($login);
            return $user;

        } catch (CustomUserMessageAuthenticationException $exception) {

            if ($exception->getCode() == 404) {
                $this->addFlash(
                    'notice',
                    'in order to do this operation you must create Create a daticash account.'
                );
                return 404;
            }
            if ($exception->getCode() == 412) {
                $this->addFlash(
                    'notice',
                    'in order to do this operation you must Activate your account.'
                );
                $response = [
                    "code" => $exception->getCode(),
                    "error_content" => $exception->getMessageData()
                ];
                return $response;
            }
            if ($exception->getCode() == 401) {
                return 401;
            }
            return $exception->getCode();

        }

    }

    public function updateWebhook(Request $request,LoggerInterface $logger)
    {


        $data = $request->getContent();
        $data = json_decode($data);

        if (!is_object($data)) {
            parse_str($request->getContent(), $output);
            $data = (object)$output;
        }

        if ($this->emptyObj($data)) {
            $data = $request->query->all();
            $data = (object)$data;
        };
        if(isset($data)) $logger->error("webhook-------------->",(array)$data);


        $data = (array)$data;

        if (isset($data["payload"])){
            $data=(array)$data["payload"];
        }else{
            return new Response(json_encode([
                'code' => '400',
                "message" => "attribute 'payload' not found in the request"
            ]), Response::HTTP_NOT_FOUND, [
                "Content-Type" => 'application/json'
            ]);

        }
        if(isset($data)) $logger->error("webhook---payload----------->".gettype ($data));


        $entityManager = $this->getDoctrine()->getManager();
        //$transaction = new DatiTransaction();
        if(isset($data["id"])){
            $transaction = $this->getDoctrine()
                ->getRepository(DatiTransaction::class)
                ->find($data['id']);

            if (!$transaction) {

                return new Response(json_encode(["status"=>"success"]), Response::HTTP_OK, [
                    "Content-Type" => 'application/json'
                ]);
            }



            if (isset($data["id"])) {
                if(isset($data["status"])) $transaction->setStatus($data["status"]);
                if(isset($data["step"])) $transaction->setStep($data["step"]);
                if(isset($data["step_description"])) $transaction->setStepDescription($data["step_description"]);
                if(isset($data["recipient_name"])) $transaction->setRecipientName($data["recipient_name"]);
               // if(isset($data["last_requested_at"])) $transaction->setLastRequestedAt($data["last_requested_at"]);


                $entityManager->persist($transaction);

                // actually executes the queries (i.e. the INSERT query)
                $entityManager->flush();

            }
        }


        return new Response(json_encode(["status"=>"success"]), Response::HTTP_OK, [
            "Content-Type" => 'application/json'
        ]);
    }
}