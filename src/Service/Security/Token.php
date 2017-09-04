<?php

namespace CurrencyRates\Service\Security;

use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use CurrencyRates\Entity\User;

class Token extends UsernamePasswordToken
{
    public function __construct(User $user, $password)
    {
        parent::__construct($user->getUsername(), $password, 'login', $user->getRoles());
    }
}
