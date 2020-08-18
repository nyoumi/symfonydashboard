<?php
namespace App\Security\User;

use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\EquatableInterface;


class WebserviceUser implements UserInterface, EquatableInterface
{
    private $username;
    private $phone_number;
    private $country_code;
    private $password;
    private $salt;
    private $roles;
    private $lastname ;
    private $firstname;
    private $currency_code ;
    private $accounts ;
    private $services ;
    private $enabled;
    private $id ;
    private $email ;
    private $is_email_activated;
    private $is_phone_activated;
    private $avatar;
    private $userInfos;

    /**
     * @return mixed
     */
    public function getEnabled()
    {
        return $this->enabled;
    }

    /**
     * @param mixed $enabled
     */
    public function setEnabled($enabled): void
    {
        $this->enabled = $enabled;
    }

    /**
     * @return mixed
     */
    public function getisEmailActivated()
    {
        return $this->is_email_activated;
    }

    /**
     * @param mixed $is_email_activated
     */
    public function setIsEmailActivated($is_email_activated): void
    {
        $this->is_email_activated = $is_email_activated;
    }

    /**
     * @return mixed
     */
    public function getisPhoneActivated()
    {
        return $this->is_phone_activated;
    }

    /**
     * @param mixed $is_phone_activated
     */
    public function setIsPhoneActivated($is_phone_activated): void
    {
        $this->is_phone_activated = $is_phone_activated;
    }

    /**
     * @return mixed
     */
    public function getAvatar()
    {
        return $this->avatar;
    }

    /**
     * @param mixed $avatar
     */
    public function setAvatar($avatar): void
    {
        $this->avatar = $avatar;
    }

    
    public function __construct( )
    {

    }
    /**
     * @param mixed $username
     */
    public function setUsername($username): void
    {
        $this->username = $username;
    }

    /**
     * @param mixed $phone_number
     */
    public function setPhoneNumber($phone_number): void
    {
        $this->phone_number = $phone_number;
    }

    /**
     * @param mixed $country_code
     */
    public function setCountryCode($country_code): void
    {
        $this->country_code = $country_code;
    }

    /**
     * @param mixed $password
     */
    public function setPassword($password): void
    {
        $this->password = $password;
    }

    /**
     * @param mixed $salt
     */
    public function setSalt($salt): void
    {
        $this->salt = $salt;
    }

    /**
     * @param mixed $roles
     */
    public function setRoles($roles): void
    {
        $this->roles = $roles;
    }


    /**
     * @param mixed $id
     */
    public function setId($id): void
    {
        $this->id = $id;
    }

    /**
     * @param mixed $email
     */
    public function setEmail($email): void
    {
        $this->email = $email;
    }

    /**
     * @param mixed $lastname
     */
    public function setLastname($lastname): void
    {
        $this->lastname = $lastname;
    }

    /**
     * @param mixed $firstname
     */
    public function setFirstname($firstname): void
    {
        $this->firstname = $firstname;
    }

    /**
     * @param mixed $currency_code
     */
    public function setCurrencyCode($currency_code): void
    {
        $this->currency_code = $currency_code;
    }

    /**
     * @param mixed $accounts
     */
    public function setAccounts($accounts): void
    {
        $this->accounts = $accounts;
    }

    /**
     * @param mixed $services
     */
    public function setServices($services): void
    {
        $this->services = $services;
    }

    public function getRoles()
    {

        // guarantee every user at least has ROLE_USER
        //$roles[] = 'ROLE_USER';

        return $this->roles;
        //return $this->roles;
    }


    public function getPassword()
    {
        return $this->password;
    }
    public function getPlainPassword()
    {
        return $this->password;
    }

    public function setPlainPassword($password): void
    {
        $this->password = $password;
    }
    public function getSalt()
    {
        return $this->salt;
    }

    public function getUsername()
    {
        return $this->username;
    }
    public function getPhoneNumber()
    {
        return $this->phone_number;
    }
    public function getCountryCode()
    {
        return $this->country_code;
    }
    public function getId()
    {
        return $this->id;
    }
    public function getEmail()
    {
        return $this->email;
    }
    public function getLastname()
    {
        return $this->lastname;
    }
    public function getFirstname()
    {
        return $this->firstname;
    }
    public function getCurrency_code()
    {
        return $this->currency_code;
    }
    public function getAccounts()
    {
        return $this->accounts;
    }
    public function getServices()
    {
        return $this->services;
    }

    public function eraseCredentials()
    {
    }

    public function isEqualTo(UserInterface $user)
    {
        if (!$user instanceof WebserviceUser) {
            return false;
        }

        if ($this->password !== $user->getPassword()) {
            return false;
        }

        if ($this->salt !== $user->getSalt()) {
            return false;
        }

        if ($this->username !== $user->getUsername()) {
            return false;
        }
        if ($this->username !== $user->getUsername()) {
            return false;
        }

        return true;
    }

    /**
     * @return mixed
     */
    public function getUserInfos()
    {
        return $this->userInfos;
    }

    /**
     * @param mixed $userInfos
     */
    public function setUserInfos($userInfos): void
    {
        $this->userInfos = $userInfos;
    }
}
