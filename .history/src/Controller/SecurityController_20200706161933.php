<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
//use App\Security\User\WebserviceUser;



class SecurityController extends AbstractController
{
    private $userProvider;

/*     public function __construct(WebserviceUser $userProvider)
    {
        $this->userProvider = $userProvider;
        // ...
    } */
    public function login(AuthenticationUtils $authenticationUtils)
        {
            echo "lllllll";
            // get the login error if there is one
            $error = $authenticationUtils->getLastAuthenticationError();

            // last username entered by the user
            $lastUsername = $authenticationUtils->getLastUsername();

            return $this->render('security/login.html.twig', [
                'last_username' => $lastUsername,
                'error'         => $error,
            ]);
        }


}
