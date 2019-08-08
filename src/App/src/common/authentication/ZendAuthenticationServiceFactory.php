<?php

namespace App\Common\Authentication;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

class ZendAuthenticationServiceFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): ZendAuthenticationService
    {
        $config = $container->get('config');

        return new ZendAuthenticationService($config);
    }
}
