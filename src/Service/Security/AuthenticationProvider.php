<?php
namespace CurrencyRates\Service\Security;
 
use Symfony\Component\Security\Core\Authentication\Provider\AuthenticationProviderInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\NonceExpiredException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use CurrencyRates\Service\Security\Token;
use Symfony\Component\HttpKernel\Exception\HttpException;
 
class AuthenticationProvider implements AuthenticationProviderInterface
{
    private $userProvider;
    private $cacheDir;

    protected $encoder;

    public function setEncoder($encoder)
    {
        $this->encoder = $encoder;
    }

    public function __construct(UserProviderInterface $userProvider, $cacheDir)
    {
        $this->userProvider = $userProvider;
        $this->cacheDir     = $cacheDir;
    }

    public function authenticate(TokenInterface $token)
    {
        $user = $this->userProvider->loadUserByUsername($token->getUsername());
        if ($user) {
            $passwordValid = $this->encoder->isPasswordValid($user, $token->getCredentials());
            if ($passwordValid) {
                $authenticatedToken = new Token($user, $token->getCredentials());
                $authenticatedToken->setUser($user);
                return $authenticatedToken;
            }
        }
        throw new HttpException(401, 'Bad credentials');
    }

    public function supports(TokenInterface $token)
    {
        return $token instanceof Token;
    }
}
