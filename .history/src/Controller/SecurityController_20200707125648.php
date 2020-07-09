<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use App\Security\User\WebserviceUserProvider;



class SecurityController extends AbstractController
{
    private $userProvider;

    public function __construct(WebserviceUserProvider $userProvider)
    {
        $this->userProvider = $userProvider;
        // ...
    }
    public function login(AuthenticationUtils $authenticationUtils)
        {
      
            // get the login error if there is one
            $error = $authenticationUtils->getLastAuthenticationError();

            // last username entered by the user
            $lastUsername = $authenticationUtils->getLastUsername();
            //$this->userProvider->loadUserByUsername($lastUsername);

            return $this->render('security/login.html.twig', [
                'last_username' => $lastUsername,
                'error'         => $error,
            ]);
        }

        
    /**
     * @Route("/logout", name="app_logout")
     */
    public function logout()
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }


}
