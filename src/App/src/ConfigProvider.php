<?php

declare(strict_types=1);

namespace App;

use App\Common\Authentication\AuthenticationService;
use App\Common\Authentication\AuthenticationServiceFactory;
use App\Common\Authentication\JwtAuthenticationMiddleware;
use App\Common\Authentication\JwtAuthenticationMiddlewareFactory;
use App\Handler\AthletesHandler;
use App\Handler\AthletesHandlerFactory;
use App\Handler\AuthenticationHandler;
use App\Handler\AuthenticationHandlerFactory;
use App\Handler\CurrentUserHandler;
use App\Handler\CurrentUserHandlerFactory;
use App\Handler\RefreshTokenHandler;
use App\Handler\RefreshTokenHandlerFactory;
use App\Handler\ActivitiesHandler;
use App\Handler\ActivitiesHandlerFactory;
use App\Model\Activities;
use App\Model\ActivitiesFactory;
use App\Common\Middleware\OverrideHttpMethodMiddleware;

class ConfigProvider
{
    public function __invoke() : array
    {
        return [
            'dependencies' => [
                'factories' => [
                    Handler\HomePageHandler::class     => Handler\HomePageHandlerFactory::class,
                    Activities::class                  => ActivitiesFactory::class,
                    AthletesHandler::class             => AthletesHandlerFactory::class,
                    AuthenticationService::class       => AuthenticationServiceFactory::class,
                    AuthenticationHandler::class       => AuthenticationHandlerFactory::class,
                    CurrentUserHandler::class          => CurrentUserHandlerFactory::class,
                    JwtAuthenticationMiddleware::class => JwtAuthenticationMiddlewareFactory::class,
                    RefreshTokenHandler::class         => RefreshTokenHandlerFactory::class,
                    ActivitiesHandler::class           => ActivitiesHandlerFactory::class,
                ],
                'invokables' => [
                    Handler\PingHandler::class          => Handler\PingHandler::class,
                    OverrideHttpMethodMiddleware::class => OverrideHttpMethodMiddleware::class,
                ],
            ],

            'jwt' => [
                'allowed_algs' => ['HS256'],
            ],

            'templates'    => [
                'paths' => [
                    'app'    => [__DIR__ . '/../templates/app'],
                    'error'  => [__DIR__ . '/../templates/error'],
                    'layout' => [__DIR__ . '/../templates/layout'],
                ],
            ],
        ];
    }
}
