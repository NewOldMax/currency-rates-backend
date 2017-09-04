<?php

namespace CurrencyRates\Service\User;

use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use CurrencyRates\Entity\User;
use CurrencyRates\Service\User\UserManager;
use HWI\Bundle\OAuthBundle\Security\Core\User\OAuthAwareUserProviderInterface;
use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;

class UserProvider implements UserProviderInterface, OAuthAwareUserProviderInterface
{
    protected $em;
    protected $manager;

    public function setEntityManager($em)
    {
        $this->em = $em;
    }

    public function setUserManager(UserManager $manager)
    {
        $this->manager = $manager;
    }

    public function loadUserByUsername($email)
    {
        if ($userData = $this->findUserByEmail($email)) {
            return $userData;
        }

        throw new AuthenticationException(
            sprintf('User with email "%s" does not exist.', $email)
        );
    }

    public function refreshUser(UserInterface $user)
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(
                sprintf('Instances of "%s" are not supported.', get_class($user))
            );
        }

        return $this->loadUserByUsername($user->getUsername());
    }

    public function loadUserByIdentity($value, $identity)
    {
        if ($userData = $this->em->getRepository(User::class)->findOneBy([$identity => $value])) {
            return $userData;
        }

        throw new AuthenticationException("User with $identity \"$value\" does not exist.");
    }

    public function supportsClass($class)
    {
        return $class === 'CurrencyRates\Entity\User';
    }

    public function findUserByEmail($email)
    {
        $userData = [];
        $email = trim($email);
        if ($user = $this->em->getRepository(User::class)->findOneBy(['email' => $email])) {
            $userData = $user;
        }
        return $userData;
    }

    /**
     * {@inheritdoc}
     */
    public function loadUserByOAuthUserResponse(UserResponseInterface $response)
    {
        return $this->findOrCreateOAuthUser($response);
    }

    private function findOrCreateOAuthUser(UserResponseInterface $response)
    {
        $owner = $response->getResourceOwner()->getName();
        $fields = $response->getResponse();
        $user = null;
        if (isset($fields['email'])) {
            $user = $this->findUserByEmail($fields['email']);
        }
        if (!$user) {
            if ($owner == 'google') {
                if (!$user = $this->em->getRepository(User::class)->findOneBy(['googleId' => $fields['id']])) {
                    $user = $this->manager->createFromGoogle($fields);
                }
            } else {
                throw new AuthenticationException(
                    sprintf('Unknown provider "%s".', $owner)
                );
            }
            if ($user) {
                $this->em->persist($user);
                $this->em->flush();
                return $user;
            }
        }
        throw new AuthenticationException(
            sprintf('Cannot authentificate user with %s provider', $owner)
        );
    }
}
