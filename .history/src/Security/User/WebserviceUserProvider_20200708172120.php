<?php
namespace App\Security\User;

use App\Security\User\WebserviceUser;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;


use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


class WebserviceUserProvider implements UserProviderInterface  
{
    private $site_url ;
    private $apikey ;
    private $user_id;
    private $client;
    

    public function __construct(HttpClientInterface $client,string $site_url)
    {
        $this->client = $client;

        $this->site_url = $site_url;




    }

    public function loadUserByUsername($username)
    {
        
        return $this->fetchUser($username);

    }

    public function refreshUser(UserInterface $user)
    {
    


        if (!$user instanceof WebserviceUser) {
            throw new UnsupportedUserException(
                sprintf('Instances of "%s" are not supported.', get_class($user))
            );
        }

        $username = $user->getUsername();

        return $this->fetchUser($username);
    }

    public function supportsClass($class)
    {
        return WebserviceUser::class === $class;
    }

    private function fetchUser($username)
    {

        // make a call to your webservice here

       $endpoint="secure/login";
        $headers=[
            'Accept' => 'application/json',
        ];
        $params=[
            'pin_code' => '12345',
            'phone_number' => '236978756',
            'country_code'=> '+237',

        ];
        try {
           // $login=$this->make_get_request($params,$headers,$endpoint);

        } catch (\Throwable $th) {
            //throw $th;
        }


        $userData  = [
            "username" => "google",
            "password" => "azerty",
        ];
        
        //var_dump($userData);
        // pretend it returns an array on success, false if there is no user

        if ($userData) {
            $password = $userData["password"];
            $username = $userData["username"];
            $salt='';
            $roles=[
                "ROLE_USER",
                "ROLE_EDITOR" ,
            ];
            $phone_number="zzzz";
            $country_code="zertyu";


            // ...
            $login= new WebserviceUser($phone_number ,$country_code,$password  ,$salt, $roles);

            return new WebserviceUser($username, $password, $salt, $roles);
        }

        throw new UsernameNotFoundException(
            sprintf('Username "%s" does not exist.', $username)
        );
    }

    public function make_get_request(array $parameters,array $header,$endpoint){


        $params="";
        foreach($parameters as $key=>$value)
        {
            $params=$params.$key."=".$value."&";
            
        }
        $params = substr($params, 0, -1);
        $params=urlencode($params);
        try {
            $response = $this->client->request(
                'GET',
                $this->site_url.$endpoint."?".$params,
                [
                    'headers' => $header,
                ]
            );
        } catch (\Exception $th) {
            
        }
     
        $statusCode = $response->getStatusCode();
        if($statusCode!=200){
          var_dump($statusCode);
        }
        $contents = $response->getContent();
        if($statusCode==200) return $response->toArray();
        return 1000;



    }
}