<?php

declare(strict_types=1);

use App\Handler\AuthenticationHandler;
use App\Common\Authentication\AuthenticationMiddleware;
use Psr\Container\ContainerInterface;
use Zend\Expressive\Application;
use Zend\Expressive\MiddlewareFactory;

return function (Application $app, MiddlewareFactory $factory, ContainerInterface $container) : void {
    $app->get('/', App\Handler\HomePageHandler::class, 'home');
    $app->get('/api/ping', App\Handler\PingHandler::class, 'api.ping');

    $app->post(
        '/api/rest/v1/authentication',
        [
            AuthenticationMiddleware::class,
            AuthenticationHandler::class,
        ],
        'authentication'
    );

    // Send a Kpi report by email
    $app->post(
        '/api/rest/v1/kpi-batch-email',
        [
            JwtAuthenticationMiddleware::class,
            AuthorizationMiddleware::class,
            KpiBatchEmailHandler::class,
        ]
    );
};
