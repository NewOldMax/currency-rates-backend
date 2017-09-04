<?php

namespace CurrencyRates\Service\JWT;

use CurrencyRates\Entity\RefreshToken;
use CurrencyRates\Entity\User;
use CurrencyRates\Service\Manager;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use CurrencyRates\Exception\TranslatedException;

class RefreshTokenManager extends Manager
{
    protected $em;

    protected $required = ['token'];
    protected $unique = ['token'];

    public function create(User $user)
    {
        $token = new RefreshToken($user);
        $this->validate($token->toArray(), RefreshToken::class);
        return $token;
    }

    public function getEntityManager()
    {
        return $this->em;
    }

    public function get($token)
    {
        if ($token = $this->em->getRepository(RefreshToken::class)->findOneBy(['token' => $token])) {
            return $token;
        }

        throw new TranslatedException(
            $this->translator,
            'entity.errors.not_found',
            404,
            ['%entity%' => 'RefreshToken', '%id%' => $id]
        );
    }

    public function update(RefreshToken $token)
    {
        $token->updateValid();
        return $token;
    }

    public function delete(RefreshToken $token)
    {
        $this->em->remove($token);
    }
}
