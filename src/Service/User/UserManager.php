<?php

namespace CurrencyRates\Service\User;

use CurrencyRates\Entity\User;
use CurrencyRates\Service\Manager;

class UserManager extends Manager
{
    protected $required = ['email', 'googleId'];
    protected $unique = ['email', 'googleId'];

    protected $encoder;

    public function setEncoder($encoder)
    {
        $this->encoder = $encoder;
        return $this;
    }

    public function create(array $fields)
    {
        $this->validate($fields, User::class);
        $fields['email'] = trim($fields['email']);
        $user = User::createFromArray($fields);
        return $user;
    }

    public static function createFromGoogle(array $fields)
    {
        $this->required = ['email', 'id'];
        $this->validate($fields, User::class);
        $user = User::createFromGoogle($fields);
        return $user;
    }

    private function setPassword(User $user)
    {
        $password = $this->encoder->encodePassword($user, $user->getPlainPassword());
        $user->setPassword($password);
        return $user;
    }

    public function get($id)
    {
        if ($user = $this->em->getRepository(User::class)->find($id)) {
            return $user;
        }

        $this->throwException(
            'entity.errors.not_found',
            404,
            ['%entity%' => 'User', '%id%' => $id]
        );
    }

    public function getByEmail($email)
    {
        if ($user = $this->em->getRepository(User::class)->findOneBy(['email' => $email])) {
            return $user;
        }

        $this->throwException(
            'user.errors.email_not_found',
            404,
            ['%email%' => $email]
        );
    }

    public function activate($id, $data)
    {
        $this->required = ['password'];
        $this->validate($data, User::class);
        $user = $this->get($id);
        if ($user->isActive()) {
            $this->throwException('user.errors.already_activated', 400);
        }
        $user->activate();
        $user->setPlainPassword($data['password']);
        $user = $this->setPassword($user);
        return $user;
    }

    public function logoutFromAllDevices(User $user)
    {
        $user->refreshSeed();
        return $user;
    }

    private function generatePassword($length)
    {
        $alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
        $pass = [];
        $alphaLength = strlen($alphabet) - 1;
        for ($i = 0; $i < $length; $i++) {
            $n = rand(0, $alphaLength);
            $pass[] = $alphabet[$n];
        }
        return implode($pass);
    }
}
