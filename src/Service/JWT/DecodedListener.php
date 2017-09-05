<?php
namespace CurrencyRates\Service\JWT;

use Symfony\Component\HttpFoundation\RequestStack;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTDecodedEvent;
use CurrencyRates\Entity\Token;
use CurrencyRates\Entity\User;

class DecodedListener
{

    private $requestStack;
    private $em;

    public function __construct(RequestStack $requestStack, $em)
    {
        $this->requestStack = $requestStack;
        $this->em = $em;
    }

    /**
     * @param JWTDecodedEvent $event
     *
     * @return void
     */
    public function onJWTDecoded(JWTDecodedEvent $event)
    {
        if (!($request = $this->requestStack->getCurrentRequest())) {
            return;
        }
        $payload = $event->getPayload();
        $request = $this->requestStack->getCurrentRequest();
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
            } elseif ($user->getSeed() != $payload['seed']) {
                $event->markAsInvalid();
            }
        }
    }
}
