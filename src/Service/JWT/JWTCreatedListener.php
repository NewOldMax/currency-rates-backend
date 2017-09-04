<?php
namespace CurrencyRates\Service\JWT;

use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTCreatedEvent;
use CurrencyRates\Entity\Token;
use CurrencyRates\Entity\User;

class JWTCreatedListener
{

    protected $em;

    public function setEntityManager($em)
    {
        $this->em = $em;
    }

    /**
     * @param AuthenticationSuccessEvent $event
     */
    public function onJWTCreated(JWTCreatedEvent $event)
    {
        if (!($request = $event->getRequest())) {
            return;
        }

        $payload = $event->getData();
        $user = $event->getUser();
        $payload += $user->toAuthArray();

        $event->setData($payload);
    }

    /**
     * @param JWTDecodedEvent $event
     *
     * @return void
     */
    public function onJWTDecoded(JWTDecodedEvent $event)
    {
        if (!($request = $event->getRequest())) {
            return;
        }

        $payload = $event->getPayload();
        $request = $event->getRequest();
        try {
            $jwt = explode('Bearer ', $request->headers->all()['authorization'][0])[1];
            if ($this->em->getRepository(Token::class)->findOneBy(['token' => $jwt])) {
                $event->markAsInvalid();
            }
        } catch (\Exception $ex) {
            $event->markAsInvalid();
        }

        if (!isset($payload['exp']) || $payload['exp'] < time()) {
            $event->markAsInvalid();
        } else {
            $user = null;
            if (isset($payload['googleId'])) {
                $user = $this->em->getRepository(User::class)->findOneBy(['googleId' => $payload['googleId']]);
            }
            if (!$user) {
                $event->markAsInvalid();
            }
        }
    }
}
