<?php

namespace App\Security\User;

use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;


/**
 * Class WebserviceUserProvider
 * @package App\Security\User
 */
class WebserviceUserProvider extends AbstractController implements UserProviderInterface
{
    /**
     * @var string
     */
    private $site_url;
    /**
     * @var string
     */
    private $apikey;
    /**
     * @var HttpClientInterface
     */
    private $client;


    /**
     * WebserviceUserProvider constructor.
     * @param HttpClientInterface $client
     * @param string $apikey
     * @param string $site_url
     */
    public function __construct(HttpClientInterface $client, string $apikey, string $site_url )
    {
        $this->client = $client;

        $this->site_url = $site_url;
        $this->apikey = $apikey;





    }

    /**
     * @param object $user
     * @return \App\Security\User\WebserviceUser|UserInterface
     */
    public function loadUserByUsername($user)
    {
        $country_code = $user->getCountryCode();
        $phone_number = $user->getPhoneNumber();
        $password = $user->getPassword();


        return $this->fetchUser($country_code, $phone_number, $password);


    }

    /**
     * @param UserInterface $user
     * @return UserInterface
     */
    public function refreshUser(UserInterface $user)
    {


        if (!$user instanceof WebserviceUser) {
            throw new UnsupportedUserException(
                sprintf('Instances of "%s" are not supported.', get_class($user))
            );
        }




        return $user;
    }

    /**
     * @param string $class
     * @return bool
     */
    public function supportsClass($class)
    {
        return WebserviceUser::class === $class;
    }

    /**
     * @param $country_code
     * @param $phone_number
     * @param $password
     * @return \App\Security\User\WebserviceUser
     */
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

    /**
     * @param array $parameters
     * @param array $header
     * @param $endpoint
     * @return array
     */
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
                    ' A problem occurred during your connection! Check that you have entered your information correctly',[],$response->getStatusCode());
            };
            if($response->getStatusCode()==404){
                $this->addFlash(
                    'error',
                    'Incorrect username or password!'
                );
                throw new CustomUserMessageAuthenticationException(
                    'Incorrect username or password!',[],$response->getStatusCode());
            };
            if($response->getStatusCode()==412){
                $this->addFlash(
                    'error',
                    'This account has not been activate yet! Check your mailbox and confirm or activate your account here.'
                );

                throw new CustomUserMessageAuthenticationException(
                    'This account has not been activate yet! Check your mailbox and confirm or activate your account here.',(array)json_decode($response->getContent(false)),$response->getStatusCode());
            };
            if($response->getStatusCode()==401){
                $this->addFlash(
                    'error',
                    'Incorrect username or password!'
                );
                throw new CustomUserMessageAuthenticationException(
                    'Incorrect username or password!',[],$response->getStatusCode());
            };

        } catch (TransportExceptionInterface|ClientExceptionInterface
        |DecodingExceptionInterface|RedirectionExceptionInterface|ServerExceptionInterface
        $e) {
            $this->addFlash(
                'error',
                'An internal problem occurred during your connection! please try again later!'
            );

            throw new CustomUserMessageAuthenticationException(
                'An internal problem occurred during your connection! please try again later!');

        }
        return null;
    }
}