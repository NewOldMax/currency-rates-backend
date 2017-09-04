<?php
namespace CurrencyRates\Service\Security;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Firewall\ListenerInterface;
use CurrencyRates\Service\Security\Token;
use CurrencyRates\Entity\User;
use CurrencyRates\Service\User\UserProvider;
use Symfony\Component\HttpKernel\Exception\HttpException;

class AuthenticationListener implements ListenerInterface
{
    protected $tokenStorage;
    protected $authenticationManager;
    protected $userProvider;

    protected $encoder;

    public function setEncoder($encoder)
    {
        $this->encoder = $encoder;
    }

    public function __construct(
        TokenStorageInterface $tokenStorage,
        AuthenticationManagerInterface $authenticationManager,
        UserProvider $userProvider
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->authenticationManager = $authenticationManager;
        $this->userProvider = $userProvider;
    }

    public function handle(GetResponseEvent $event)
    {
        $request = $event->getRequest();
        try {
            $user = $this->userProvider->loadUserByUsername($request->request->get('email'));
        } catch (\Exception $e) {
            $this->tokenStorage->setToken(null);
            throw new HttpException(404, $e->getMessage());
        }
        $token = new Token($user, $request->request->get('password'));
        $token->setUser($user);
        $token->created = time();

        try {
            if ($this->authenticationManager->authenticate($token)) {
                $this->tokenStorage->setToken($token);
            }
            return;
        } catch (AuthenticationException $failed) {
            $this->tokenStorage->setToken(null);
            return;
        }

        // By default deny authorization
        $response = new Response();
        $response->setStatusCode(Response::HTTP_FORBIDDEN);
        $event->setResponse($response);
    }
}
