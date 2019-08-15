<?php

declare(strict_types=1);

use App\Common\Authentication\JwtAuthenticationMiddleware;
use App\Handler\AuthenticationHandler;
use App\Handler\CurrentUserHandler;
use App\Handler\WeekHandler;
use App\Handler\RefreshTokenHandler;
use Psr\Container\ContainerInterface;
use Zend\Expressive\Application;
use Zend\Expressive\MiddlewareFactory;

return function (Application $app, MiddlewareFactory $factory, ContainerInterface $container) : void {
    $app->get('/', App\Handler\HomePageHandler::class, 'home');
    $app->get('/api/ping', App\Handler\PingHandler::class, 'api.ping');

    $app->get(
        '/api/rest/v1/week',
        [
            JwtAuthenticationMiddleware::class,
            // TODO add authorization which test existing user
            WeekHandler::class,
        ],
        'week'
    );

    $app->post('/api/rest/v1/signin', AuthenticationHandler::class, 'signin');

    $app->get('/api/rest/v1/refresh-token', RefreshTokenHandler::class, 'refresh-token');

    $app->get('/api/rest/v1/user/current', CurrentUserHandler::class, 'user-current');

};
