<?php
namespace CurrencyRates\Service\JWT;

use Symfony\Component\HttpFoundation\RequestStack;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTCreatedEvent;
use CurrencyRates\Entity\Token;
use CurrencyRates\Entity\User;

class JWTCreatedListener
{

    private $requestStack;
    private $em;

    public function __construct(RequestStack $requestStack, $em)
    {
        $this->requestStack = $requestStack;
        $this->em = $em;
    }

    /**
     * @param AuthenticationSuccessEvent $event
     */
    public function onJWTCreated(JWTCreatedEvent $event)
    {
        $payload = $event->getData();
        $user = $event->getUser();
        $payload += $user->toAuthArray();

        $event->setData($payload);
    }
}
