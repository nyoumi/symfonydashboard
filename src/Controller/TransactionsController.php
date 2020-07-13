<?php
/**
 * Created by IntelliJ IDEA.
 * User: GOOGLE
 * Date: 11/07/2020
 * Time: 10:25
 */

namespace App\Controller;

use App\Entity\Transaction;

use Doctrine\ORM\Mapping as ORM;
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


/**
 * @ORM\Entity
 * @ORM\Table(name="operations_controller")
 */
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
        $endpoint="mobilemoney/new";
        $headers=[
            'Accept' => 'application/json',
            "apikey"=> $this->apikey
        ];


        $response=$this->make_get_request((array)$params,$headers,$endpoint);
        $response=$this->handle_response($response);
        return $response;


    }

    public function start()
    {
        return $this->render('pages/operation.html.twig', [

        ]);
    }

    /* public function register(Request $request): Response
     {
         $user = new WebserviceUser();



         $form = $this->createForm(RegistrationFormType::class, $user);

         //$form->handleRequest($request);


         if ($request->getMethod() == "POST" ) {
             $user->setPhoneNumber($request->request->get('phone_number'));
             $user->setCountryCode($request->request->get('country_code'));
             $user->setPassword($request->request->get('new_password'));
             $user->setLastname($request->request->get('lastname'));
             $user->setFirstname($request->request->get('firstname'));
             $user->setEmail($request->request->get('email'));


             $endpoint="user/new";
             $headers=[
                 'Accept' => 'application/json',
                 "apikey"=> $this->apikey
             ];

             $params=[
                 'phone_number' => $user->getPhoneNumber(),
                 'email' =>  $user->getEmail(),
                 'country_code'=> $user->getCountryCode(),
                 'plain_password'=>$user->getPassword(),
                 'lastname'=>$user->getLastname(),
                 'firstname' =>$user->getFirstname()



             ];
             $this->user_id=$this->make_get_request($params,$headers,$endpoint);
             if(isset($this->user_id) && !empty($this->user_id)){
                 return $this->redirectToRoute('home');
             }




 /*

             // do anything else you need here, like send an email
             return $this->render('registration/registration.html.twig', [
                 'registrationForm' => $form->createView(),
             ]);
             //return $this->redirectToRoute('home');
         }

         return $this->render('registration/registration.html.twig', [
             'registrationForm' => $form->createView(),
         ]);
     }*/
    public function make_get_request(array $parameters, array $header, $endpoint)
    {
/*        $myArr = '{
    "code": 404,
    "message": "empty list returned"
}';*/
        //return         $this->user_id=$this->getUser()->getid();

        //return json_encode($myArr);


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

            if($response->getStatusCode()==200 && $response->getStatusCode()==201){

                return $response->toArray();
                //return $response->toArray();
            }else{
                var_dump($response->getInfo());
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

        return new Response(json_encode($res), Response::HTTP_OK,[
            "Content-Type"=>'application/json'
        ]);
    }
}
