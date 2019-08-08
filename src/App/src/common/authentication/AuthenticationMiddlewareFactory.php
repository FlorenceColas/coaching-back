<?php

declare(strict_types=1);

namespace App\Common\Authentication;

use Psr\Container\ContainerInterface;
use Zend\Expressive\Authentication\Exception;

class AuthenticationMiddlewareFactory
{
    public function __invoke(ContainerInterface $container) : AuthenticationMiddleware
    {
        $authentication = $container->has(ZendAuthenticationService::class) ?
                          $container->get(ZendAuthenticationService::class) :
                          null;
        if (null === $authentication) {
            throw new Exception\InvalidConfigException(
                'Authentication service is missing'
            );
        }

        return new AuthenticationMiddleware($authentication);
    }
}
