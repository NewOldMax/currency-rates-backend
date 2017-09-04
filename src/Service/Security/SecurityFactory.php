<?php
 
namespace CurrencyRates\Service\Security;
 
use Symfony\Component\DependencyInjection\DefinitionDecorator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Bundle\SecurityBundle\DependencyInjection\Security\Factory\FormLoginFactory;
 
class SecurityFactory extends FormLoginFactory
{
    public function getKey()
    {
        return 'webservice_login';
    }

    public function getPosition()
    {
        return 'pre_auth';
    }
 
    public function create(ContainerBuilder $container, $id, $config, $userProvider, $defaultEntryPoint)
    {
        $providerId = 'app_auth_provider.'.$id;
        $container
            ->setDefinition($providerId, new DefinitionDecorator('app_auth_provider'))
            ->replaceArgument(0, new Reference($userProvider))
        ;

        $listenerId = 'app_auth_listener.'.$id;
        $listener = $container->setDefinition($listenerId, new DefinitionDecorator('app_auth_listener'));

        return array($providerId, $listenerId, $defaultEntryPoint);
    }
}
