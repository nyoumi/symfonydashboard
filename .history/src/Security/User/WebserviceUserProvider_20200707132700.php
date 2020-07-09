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


class WebserviceUserProvider extends Controller implements UserProviderInterface  
{
    private $site_url ;
    private $apikey ;
    private $user_id;

    public function __construct(HttpClientInterface $client)
    {
        $this->client = $client;

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
  
        $this->apikey = $this->getParameter('app.apikey');
        $this->site_url = $this->getParameter('app.site_url');
        $DatiController = $this->get('request_service');
        
        $DatiController->make_get_request([],[],"secure");
        // make a call to your webservice here
       // $url = "https://api.daticash.com/api/secure/login?phone_number=".$curl_data['phone_number']."&pin_code=".$curl_data['pin_code']."&country_code=".$curl_data['country_code'];

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
}