<?php

namespace features\CurrencyRates\Context;

use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\Symfony2Extension\Context\KernelAwareContext;
use Behat\Symfony2Extension\Context\KernelDictionary;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use features\CurrencyRates\Support\ConsoleContextTrait;
use Symfony\Component\Console\Output\BufferedOutput;
use features\CurrencyRates\Support\RequestTrait;
use features\CurrencyRates\Support\DoctrineHelperTrait;
use CurrencyRates\Entity\User;
use CurrencyRates\Entity\Pipeline;
use CurrencyRates\Service\Security\Token;

abstract class CommonContext implements SnippetAcceptingContext, KernelAwareContext
{
    use KernelDictionary;
    use RequestTrait;
    use DoctrineHelperTrait;
    use ConsoleContextTrait;

    protected $authorized_user;

    public function getEntityManager() : EntityManager
    {
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        if (!$em->isOpen()) {
            $em = $em->create($em->getConnection(), $em->getConfiguration());
        }
        return $em;
    }

    public function createUser($email, $role, $password = '‡') : User
    {
        $user = $this->getContainer()->get('app_user_manager')->createFromGoogle(
            [
                'email' => $email,
                'role' => $role,
                'password' => $password,
                'name' => 'name',
                'id' => '123',
            ]
        );

        $em = $this->getEntityManager();

        $user->activate();

        $em->persist($user);
        $em->flush();

        return $user;
    }

    /**
     * @Given I signed in
     */
    public function iSignedIn() : void
    {
        $this->iSignedInAs(User::ROLE_USER);
    }

    /**
     * @Given I signed in as :role
     */
    public function iSignedInAs($role) : void
    {
        $this->authorized_user = $user = $this->createUser('user'.rand().'@example.com', $role, '‡');
        $this->generateTokenFor($user);
    }

    /**
     * @param User $user
     */
    protected function generateTokenFor(User $user) : void
    {
        $token = $this->getContainer()->get('lexik_jwt_authentication.jwt_manager')->create($user);
        $this->authorized($token);
    }
}
