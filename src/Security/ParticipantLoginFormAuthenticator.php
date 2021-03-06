<?php
// src/Security/LoginFormAuthenticator.php
namespace App\Security;

use App\Security\User\WebserviceUser;
use Doctrine\ORM\EntityManagerInterface;
use http\Exception\BadHeaderException;
use HttpEncodingException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\Exception\InvalidCsrfTokenException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Guard\Authenticator\AbstractFormLoginAuthenticator;
use Symfony\Component\Security\Guard\PasswordAuthenticatedInterface;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

class ParticipantLoginFormAuthenticator extends AbstractFormLoginAuthenticator implements PasswordAuthenticatedInterface
{
    use TargetPathTrait;

    public const LOGIN_ROUTE = 'login';

    private $entityManager;
    private $urlGenerator;
    private $csrfTokenManager;
    private $passwordEncoder;

    private $apikey ;
    private $request;


    public function __construct(EntityManagerInterface $entityManager, UrlGeneratorInterface $urlGenerator, CsrfTokenManagerInterface $csrfTokenManager, UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->entityManager = $entityManager;
        $this->urlGenerator = $urlGenerator;
        $this->csrfTokenManager = $csrfTokenManager;
        $this->passwordEncoder = $passwordEncoder;
    }

    public function supports(Request $request)
    {
        return self::LOGIN_ROUTE === $request->attributes->get('_route')
            && $request->isMethod('POST');
    }

    public function getCredentials(Request $request)
    {

        $credentials = [
            'country_code' => $request->request->get('country_code'),
            'username' => $request->request->get('phone_number'),
            'phone_number' => $request->request->get('phone_number'),
            'password' => $request->request->get('password'),
            'csrf_token' => $request->request->get('_csrf_token'),
        ];
        $request->getSession()->set(
            Security::LAST_USERNAME,
            $credentials['phone_number']
        );
        $request->getSession()->set(
            "password",
            $credentials['password']
        );
        return $credentials;
    }

    public function getUser($credentials, UserProviderInterface $userProvider)
    {

        $token = new CsrfToken('authenticate', $credentials['csrf_token']);
        if (!$this->csrfTokenManager->isTokenValid($token)) {

            //return new RedirectResponse($this->urlGenerator->generate('login'));

            throw new InvalidCsrfTokenException();
        }
        if (empty($credentials["password"])) {
            throw new CustomUserMessageAuthenticationException('Le mot de passe ne peut être vide!');

        }
        if (empty($credentials["phone_number"])) {
            throw new CustomUserMessageAuthenticationException('Le numéro de téléphone  ne peut être vide!');

        }
        if (empty($credentials["country_code"])) {
            throw new CustomUserMessageAuthenticationException('Le code pays  ne peut être vide!');

        }
        if (!is_int( (int)$credentials["country_code"] )) {
            throw new CustomUserMessageAuthenticationException('Le code pays  doit être un nombre!');

        }

        $login= new WebserviceUser();
        //$credentials["country_code"] ,$credentials["phone_number"],$credentials["password"]  ,$salt, $roles
        $login->setCountryCode($credentials["country_code"] );
        $login->setPhoneNumber($credentials["phone_number"]);
        $login->setPassword($credentials["password"]);

        $user= $userProvider->loadUserByUsername($login);

        if (!$user) {
            // fail authentication with a custom error
            throw new CustomUserMessageAuthenticationException('Identifiant ou mot de passe incorrect!');
        }


        return $user;
    }

    public function checkCredentials($credentials, UserInterface $user)
    {



        return true;
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function getPassword($credentials): ?string
    {
        return $credentials['password'];
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {


        if (    strpos($this->getTargetPath($request->getSession(), $providerKey), 'operation')) {
            return new RedirectResponse($this->urlGenerator->generate('home'));
        }
        if ($targetPath = $this->getTargetPath($request->getSession(), $providerKey)) {
            return new RedirectResponse($targetPath);
        }

        return new RedirectResponse($this->urlGenerator->generate('home'));
        //throw new \Exception('TODO: provide a valid redirect inside '.__FILE__);
    }
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        if($exception->getCode()==412){
            var_dump($exception->getMessageData());
            return new RedirectResponse($this->urlGenerator->generate('activation',[
                "user_id"=>$exception->getMessageData()["user_id"],
                "email"=>$exception->getMessageData() ["email"]

            ]));
        } else{

            throw new CustomUserMessageAuthenticationException($exception->getMessage(),$exception->getMessageData(),$exception->getCode());

        }

    }

    protected function getLoginUrl()
    {

        return $this->urlGenerator->generate(self::LOGIN_ROUTE);
    }
}
