<?php

namespace App\Common\Authentication;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

class JwtAuthenticationMiddlewareFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): JwtAuthenticationMiddleware
    {
        $config = $container->get('config');

        return new JwtAuthenticationMiddleware($config);
    }
}
