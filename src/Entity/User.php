<?php
namespace CurrencyRates\Entity;

use CurrencyRates\Service\BasicEntity;
use Symfony\Component\Security\Core\User\UserInterface;

class User extends BasicEntity implements UserInterface
{
    const ROLE_USER = 'user';

    const ROLES = [
        self::ROLE_USER,
    ];

    //Not mapped
    protected $plainPassword;

    //Properties
    protected $id;
    protected $name;
    protected $email;
    protected $active;
    protected $createdAt;
    protected $role;
    protected $googleId;
    protected $seed;

    public function __construct($fields)
    {
        $this->name = $fields['name'] ?? null;
        $this->email = $fields['email'] ?? null;
        $this->plainPassword = $fields['password'] ?? null;
        $this->salt = $fields['salt'] ?? null;
        $this->role = self::ROLE_USER;
        $this->active = false;
        $this->createdAt = new \DateTime();
        $this->seed = bin2hex(openssl_random_pseudo_bytes(16));
    }

    public function __toString()
    {
        return $this->email;
    }

    protected function patchable()
    {
        return ['name'];
    }

    public function toArray()
    {
        return [
            'id' => $this->getId(),
            'name' => $this->name,
            'email' => $this->email,
            'role' => $this->role,
            'active' => $this->active,
        ];
    }

    public function toAuthArray()
    {
        return [
            'id' => $this->getId(),
            'email' => $this->email,
            'role' => $this->role,
            'name' => $this->name,
            'googleId' => $this->googleId,
            'active' => $this->active,
            'seed' => $this->seed,
        ];
    }

    public static function createFromGoogle($fields)
    {
        $instance = new self($fields);
        $instance->setGoogleId($fields['id']);
        $instance->activate();
        return $instance;
    }

    public static function createFromArray($fields)
    {
        return new self($fields);
    }

    public function eraseCredentials()
    {
    }

    public function refreshSeed()
    {
        $this->seed = bin2hex(openssl_random_pseudo_bytes(16));
        return $this;
    }

    public function getUsername()
    {
        return $this->email;
    }

    public function getPlainPassword()
    {
        return $this->plainPassword;
    }

    public function setPlainPassword($password)
    {
        $this->plainPassword = $password;
    }

    public function setPassword($password)
    {
        $this->password = $password;
    }

    public function getPassword()
    {
        return $this->password;
    }

    public function getSalt()
    {
        return $this->salt;
    }

    public function getRoles()
    {
        return array('ROLE_USER', $this->getRoleForSecurity());
    }

    public function getRoleForSecurity()
    {
        // for future roles
        // $roles = [
        //     self::ROLE_ADMIN => 'ROLE_ADMIN',
        //     self::ROLE_SUPER_ADMIN => 'ROLE_SUPER_ADMIN'
        // ];
        return isset($roles[$this->getRole()]) ? $roles[$this->getRole()] : 'ROLE_USER';
    }

    public function isUser()
    {
        return $this->role === self::ROLE_USER;
    }

    public function getRole()
    {
        return $this->role;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getLastName()
    {
        return $this->lastName;
    }

    public function isActive()
    {
        return $this->active;
    }

    public function activate()
    {
        $this->active = true;
    }

    public function deactivate()
    {
        $this->active = false;
    }

    public function setRole($role)
    {
        $this->role = $role;
    }

    public function setGoogleId($googleId)
    {
        $this->googleId = $googleId;
    }

    public function getSeed()
    {
        return $this->seed;
    }

    public function getCreatedAt(string $format = 'Y-m-d')
    {
        return $this->formatDate($this->createdAt, $format);
    }
}
