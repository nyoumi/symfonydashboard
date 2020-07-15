<?php

namespace App\Controller;

use App\Form\RegistrationFormType;
use App\Security\User\WebserviceUser;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;


class RegistrationController extends Controller
{
    private $client;
    private $site_url ;
    private $apikey ;
    private $user_id;

    public function __construct(HttpClientInterface $client)
    {
        $this->client = $client;

    }

    /**
     * @Route("/register", name="register")
     * @param Request $request
     * @return Response
     */
    public function register(Request $request): Response
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






            // do anything else you need here, like send an email
            return $this->render('registration/registration.html.twig', [
                'registrationForm' => $form->createView(),
            ]);
            //return $this->redirectToRoute('home');
        }

        return $this->render('registration/registration.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }
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

                $content = $response->getContent(false);
                $this->addFlash(
                    'error',
                    $content
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



    }
}
