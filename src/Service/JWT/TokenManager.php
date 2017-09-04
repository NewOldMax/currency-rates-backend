<?php

namespace CurrencyRates\Service\JWT;

use CurrencyRates\Entity\Token;
use CurrencyRates\Entity\User;
use CurrencyRates\Service\Manager;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use CurrencyRates\Exception\TranslatedException;

class TokenManager extends Manager
{
    protected $em;
    protected $encoder;

    protected $required = ['token'];
    protected $unique = ['token'];

    public function setEncoder(Encoder $encoder)
    {
        $this->encoder = $encoder;
    }

    public function addToBlackList($token)
    {
        if ($decodedToken = $this->encoder->decode($token)) {
            $fields = [
                'token' => $token,
                'expiredAt' => new \DateTime(date('Y-m-d H:i:s', $decodedToken['exp']))
            ];
            $this->validate($fields, Token::class);
            $token = Token::createFromArray($fields);
            return $token;
        }

        throw new \Exception('jwt.error.token_invalid');
    }

    public function inBlackList($token)
    {
        try {
            return $this->get($token);
        } catch (\Exception $ex) {
            return false;
        }
    }

    public function get($token)
    {
        if ($token = $this->em->getRepository(Token::class)->findOneBy(['token' => $token])) {
            return $token;
        }

        throw new TranslatedException($this->translator, 'jwt.errors.token_not_found', 404, ['%token%' => $token]);
    }

    public function delete(Token $token)
    {
        $this->em->remove($token);
    }
}
