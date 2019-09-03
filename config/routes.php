<?php

declare(strict_types=1);

use App\Common\Authentication\JwtAuthenticationMiddleware;
use App\Handler\AthletesHandler;
use App\Handler\AuthenticationHandler;
use App\Handler\CurrentUserHandler;
use App\Handler\ActivitiesHandler;
use App\Handler\RefreshTokenHandler;
use Psr\Container\ContainerInterface;
use Zend\Expressive\Application;
use Zend\Expressive\MiddlewareFactory;

return function (Application $app, MiddlewareFactory $factory, ContainerInterface $container) : void {
    $app->get('/', App\Handler\HomePageHandler::class, 'home');
    $app->get('/api/ping', App\Handler\PingHandler::class, 'api.ping');

    $app->post('/api/rest/v1/signin', AuthenticationHandler::class, 'signin');

    $app->get('/api/rest/v1/refresh-token', RefreshTokenHandler::class, 'refresh-token');

    $app->get('/api/rest/v1/user/current', CurrentUserHandler::class, 'user-current');

    $app->get('/api/rest/v1/activities/:week/:year/:athlete',
        [
            JwtAuthenticationMiddleware::class,
            // TODO add authorization which test existing user
            ActivitiesHandler::class,
        ],
        'week-activities'
    );

    $app->get('/api/rest/v1/athletes/:coach',
        [
            JwtAuthenticationMiddleware::class,
            AthletesHandler::class,
        ],
        'athletes'
    );

    $app->delete('/api/rest/v1/activities/:id',
        [
            JwtAuthenticationMiddleware::class,
            ActivitiesHandler::class,
        ],
        'activities-delete'
    );

    $app->post('/api/rest/v1/activities',
        [
            JwtAuthenticationMiddleware::class,
            ActivitiesHandler::class,
        ],
        'activities-create'
    );

    $app->put('/api/rest/v1/activities/:id',
        [
            JwtAuthenticationMiddleware::class,
            ActivitiesHandler::class,
        ],
        'activities-update'
    );
};
