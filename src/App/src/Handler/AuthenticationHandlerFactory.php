<?php

declare(strict_types=1);

namespace App\Handler;

use App\Common\Authentication\AuthenticationService;
use Psr\Container\ContainerInterface;
use Psr\Http\Server\RequestHandlerInterface;

class AuthenticationHandlerFactory
{
    public function __invoke(ContainerInterface $container) : RequestHandlerInterface
    {
        $authService = $container->get(AuthenticationService::class);

        return new AuthenticationHandler($authService);
    }
}
