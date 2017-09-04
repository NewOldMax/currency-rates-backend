<?php

namespace CurrencyRates;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use CurrencyRates\Service\Security\SecurityFactory;

class AppBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $loader = new YamlFileLoader($container, new FileLocator([
            __DIR__ . '/Resources/config'
        ]));
        $loader->load('services.yml');

        $extension = $container->getExtension('security');
        $extension->addSecurityListenerFactory(new SecurityFactory());
    }
}
