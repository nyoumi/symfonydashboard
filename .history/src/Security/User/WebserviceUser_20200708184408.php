<?php
// src/Security/User/WebserviceUser.php
namespace App\Security\User;

use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\EquatableInterface;

class WebserviceUser implements UserInterface
{
    private $username;
    private $phone_number;
    private $country_code;
    private $password;
    private $salt;
    private $roles;

    public function __construct( $country_code,$phone_number ,$password, $salt, array $roles)
    {
        $this->username = $phone_number;
        $this->phone_number=$phone_number;
        $this->country_code = $country_code;
        $this->password = $password;
        $this->salt = $salt;
        $this->roles = $roles;
    }

    public function getRoles()
    {
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
        //return $this->roles;
    }

    public function getPassword()
    {
        return $this->password;
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

    public function eraseCredentials()
    {
    }

    
}
