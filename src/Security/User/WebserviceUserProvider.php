<?php

namespace App\Security\User;

use App\Security\User\WebserviceUser;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;


use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;


class WebserviceUserProvider extends AbstractController implements UserProviderInterface
{
    private $site_url;
    private $apikey;
    private $client;


    public function __construct(HttpClientInterface $client,string $apikey, string $site_url )
    {
        $this->client = $client;

        $this->site_url = $site_url;
        $this->apikey = $apikey;





    }

    public function loadUserByUsername($user)
    {
        $country_code = $user->getCountryCode();
        $phone_number = $user->getPhoneNumber();
        $password = $user->getPassword();


        return $this->fetchUser($country_code, $phone_number, $password);


    }

    public function refreshUser(UserInterface $user)
    {


        if (!$user instanceof WebserviceUser) {
            throw new UnsupportedUserException(
                sprintf('Instances of "%s" are not supported.', get_class($user))
            );
        }




        return $user;
    }

    public function supportsClass($class)
    {
        return WebserviceUser::class === $class;
    }

    private function fetchUser($country_code, $phone_number, $password)
    {



        $endpoint = "user/authenticate";
        $headers = [
            'Accept' => 'application/json',
            'apikey' =>  $this->apikey
        ];
        $params = [
            'password' => $password,
            'phone_number' => $phone_number,
            'country_code' => $country_code,

        ];

            $login = $this->make_get_request($params, $headers, $endpoint);



            if (isset($login) && isset($login["id"]) ) {
                $webUser=new WebserviceUser();
                $webUser->setId($login["id"]);
                $webUser->setEmail($login["email"]);
                $webUser->setAccounts($login["accounts"]);
                $webUser->setCurrencyCode($login["currency_code"]);
                $webUser->setFirstname($login["firstname"]);
                $webUser->setLastname($login["lastname"]);
                $webUser->setCountryCode($country_code);
                $webUser->setPhoneNumber($phone_number);
                $webUser->setRoles($login["roles"]);
                $webUser->setSalt("");
                $webUser->setUsername($login["firstname"]);


                return $webUser;


            }
        throw new CustomUserMessageAuthenticationException(
            'Identifiant ou mot de passe incorrect!');






        //var_dump($userData);
        // pretend it returns an array on success, false if there is no user



       /* throw new UsernameNotFoundException(
            sprintf('Username "%s" does not exist.', $phone_number)
        );*/
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

            if($response->getStatusCode()==200){
                return $response->toArray();
            };
            if($response->getStatusCode()==400){
                throw new CustomUserMessageAuthenticationException(
                    ' Un problème est survenu lors de votre connexion! Veuillez que vous avez bien saisi vos informations',[$response->getStatusCode()]);
            };
            if($response->getStatusCode()==404){
                throw new CustomUserMessageAuthenticationException(
                    'Identifiant ou mot de passe incorrect!',[$response->getStatusCode()]);
            };




        } catch (TransportExceptionInterface $e) {

            throw new CustomUserMessageAuthenticationException(
                ' Un problème interne est survenu lors de votre connexion! Veuillez réessayer plus tard');

        } catch (ClientExceptionInterface $e) {

        } catch (DecodingExceptionInterface $e) {
        } catch (RedirectionExceptionInterface $e) {
        } catch (ServerExceptionInterface $e) {
        }


   /*     $statusCode = $response->getStatusCode();
        if ($statusCode != 200) {
            var_dump($statusCode);
        }
        $contents = $response->getContent();
        if ($statusCode == 200) return $response->toArray();
        return 1000;*/


    }
}