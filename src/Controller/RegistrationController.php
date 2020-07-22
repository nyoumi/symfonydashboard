<?php

namespace App\Controller;

use App\Form\RegistrationFormType;
use App\Security\User\WebserviceUser;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;


/**
 * Class RegistrationController
 * @package App\Controller
 */
class RegistrationController extends Controller
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
     * @var
     */
    private $user_id;

    /**
     * RegistrationController constructor.
     * @param HttpClientInterface $client
     */
    public function __construct(HttpClientInterface $client)
    {
        $this->client = $client;

    }


    /**
     * @param Request $request
     * @return Response
     */
    public function register(Request $request): Response
    {

        $user = new WebserviceUser();
        //if()



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

            $this->user_id=$this->make_post_request($params,$headers,$endpoint);


            if(isset($this->user_id) && !empty($this->user_id)){
                $session = $this->get('session');


                if ($session->has("transaction")){

                    $response = $this->forward('App\Controller\TransactionsController::createAction', [

                    ]);

                    return $response;
                        //$this->redirectToRoute('transactions_create_anonymous',[$transaction]);


                }

                return $this->render('pages/activations.html.twig',
                    ["user_id"=>$this->user_id["id"],
                        "user_email"=>$params["email"]
                    ]);
               // return $this->redirectToRoute('activate');
            }






            // do anything else you need here, like send an email
            return $this->render('registration/registration.html.twig', [
                'registrationForm' => $form->createView(),
            ]);
            //return $this->redirectToRoute('home');
        }

        return $this->render('registration/registration.html.twig', [
            'registrationForm' => $form->createView()
        ]);
    }
    /*
     *
     */
    /**
     * @param array $parameters
     * @param array $header
     * @param $endpoint
     * @return null si la requete s'est mal passé et la réponse si la requete s'est bien passée
     */
    public function make_post_request(array $parameters, array $header, $endpoint)
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
                ]
            );

            if($response->getStatusCode()==201){
                $this->addFlash(
                    'success',
                    'User successfully created!'
                );
                //return "id";
                return $response->toArray();
            };
            if($response->getStatusCode()==400){


                $this->addFlash(
                    'error',
                    json_decode($response->getContent(false))[0]->message." Erreur ".$response->getStatusCode()
                );
                return null;
            };
            if($response->getStatusCode()==409){
                $this->addFlash(
                    'error',
                    'Operation failed, this phone number or email already exists!'
                );
                return null;
            };

            $this->addFlash(
                'error',
                'An internal error has occurred please try again later! Erreur:'.$response->getStatusCode()
            );
            return null;




        } catch (TransportExceptionInterface $e) {

            $this->addFlash(
                'error',
                'An internal error has occurred please try again later!'
            );
            return null;
        } catch (ClientExceptionInterface $e) {

        } catch (DecodingExceptionInterface $e) {
        } catch (RedirectionExceptionInterface $e) {
        } catch (ServerExceptionInterface $e) {
        }

        $this->addFlash(
            'error',
            'An internal error has occurred please try again later!'
        );
        return null;

    }
}
