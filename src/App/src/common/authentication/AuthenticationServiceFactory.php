<?php

declare(strict_types=1);

namespace App\Common\Authentication;

use Psr\Container\ContainerInterface;
use Zend\Authentication\Adapter\AdapterInterface as AuthenticationAdapterInterface;
use Zend\Authentication\AuthenticationService;
use Zend\Authentication\AuthenticationServiceInterface;
use Zend\Authentication\Storage\StorageInterface as AuthenticationStorageInterface;

class AuthenticationServiceFactory
{
    public function __invoke(ContainerInterface $container): AuthenticationServiceInterface
    {
        $adapter = $container->get(AuthenticationAdapterInterface::class);
        $storage = $container->get(AuthenticationStorageInterface::class);

        return new AuthenticationService($storage, $adapter);
    }
}
