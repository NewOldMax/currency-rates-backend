<?php

namespace CurrencyRates\Service\JWT;

use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationSuccessEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Events;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTManager;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;
use Symfony\Component\Templating\EngineInterface;
use CurrencyRates\Entity\RefreshToken;

class AuthenticationSuccessHandler implements AuthenticationSuccessHandlerInterface
{
    /**
     * @var JWTManager
     */
    protected $jwtManager;

    /**
     * @var EventDispatcherInterface
     */
    protected $dispatcher;
    protected $refreshManager;
    protected $templating;

    /**
     * @param JWTManager               $jwtManager
     * @param EventDispatcherInterface $dispatcher
     */
    public function __construct(
        JWTManager $jwtManager,
        EventDispatcherInterface $dispatcher,
        RefreshTokenManager $refreshManager,
        EngineInterface $templating
    ) {
        $this->jwtManager = $jwtManager;
        $this->dispatcher = $dispatcher;
        $this->refreshManager = $refreshManager;
        $this->templating = $templating;
    }

    /**
     * {@inheritDoc}
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token)
    {
        $user = $token->getUser();
        $jwt  = $this->jwtManager->create($user);

        $jwtEm = $this->refreshManager->getEntityManager();

        $refreshToken = $this->refreshManager->create($user);
        if ($tokens = $jwtEm->getRepository(RefreshToken::class)->findBy(['user' => $user])) {
            foreach ($tokens as $key => $value) {
                $jwtEm->remove($value);
            }
        }
        $jwtEm->persist($refreshToken);
        $jwtEm->flush();

        $response = new Response();
        $event    = new AuthenticationSuccessEvent(
            ['token' => $jwt, 'refreshToken' => $refreshToken->getToken()],
            $user,
            $request,
            $response
        );

        $this->dispatcher->dispatch(Events::AUTHENTICATION_SUCCESS, $event);
        $response->setContent($this->templating->render('AppBundle:auth:social.html.twig', [
            'jwt' => $jwt,
            'refreshToken' => $refreshToken->getToken(),
        ]));

        return $response;
    }
}
