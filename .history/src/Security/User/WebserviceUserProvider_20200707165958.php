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


class WebserviceUserProvider extends AbstractController implements UserProviderInterface  
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
       // $url = "https://api.daticash.com/api/secure/login?phone_number=".$curl_data['phone_number']."&pin_code=".$curl_data['pin_code']."&country_code=".$curl_data['country_code'];

       $endpoint="secure/login";
        $headers=[
            'Accept' => 'application/json',
        ];
        $params=[
            'pin_code' => 'asc',
            'phone_number' => '23',
            'country_code'=> '+237',

        ];
        $login=$this->make_get_request($params,$headers,$endpoint);
        var_dump($login);


        $userData  = [
            "username" => "paul",
            "password" => "azerty",
        ];
        //var_dump($userData);
        // pretend it returns an array on success, false if there is no user

        if ($userData) {
            $password = $userData["password"];
            $username = $userData["username"];
            $salt='';
            $roles=[
                "admin",
                "editor" ,
            ];;


            // ...

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
        } catch (\Throwable $th) {
            
        }
     
        $statusCode = $response->getStatusCode();
        if($statusCode!=200){
          var_dump($statusCode)
        }
        $contents = $response->getContent();
        var_dump($contents)  ;
        if($statusCode==200) return $response->toArray();
        return null;



    }
}