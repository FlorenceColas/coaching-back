<?php

declare(strict_types=1);

namespace App;

use App\Common\Authentication\Adapter\Ldap;
use App\Common\Authentication\AuthenticationServiceFactory;
use App\Common\Authentication\AuthenticationMiddleware;
use App\Common\Authentication\AuthenticationMiddlewareFactory;
use App\Common\Authentication\ZendAuthentication;
use App\Common\Authentication\ZendAuthenticationFactory;
use App\Common\Authentication\ZendAuthenticationService;
use App\Common\Authentication\ZendAuthenticationServiceFactory;
use Zend\Authentication\Adapter\AdapterInterface as AuthenticationAdapterInterface;
use Zend\Authentication\AuthenticationService;
use Zend\Authentication\Storage\NonPersistent;
use Zend\Authentication\Storage\StorageInterface as AuthenticationStorageInterface;
use Zend\Expressive\Authentication\AuthenticationInterface;
use Zend\Expressive\Authentication\UserInterface;
use Zend\Expressive\Authorization\Acl\ZendAcl;
use Zend\Expressive\Authorization\AuthorizationInterface;

class ConfigProvider
{
    public function __invoke() : array
    {
        return [
            'dependencies' => [
                'aliases' => [
                    AuthenticationAdapterInterface::class => Ldap::class,
                    AuthenticationStorageInterface::class => NonPersistent::class,
                    AuthorizationInterface::class         => ZendAcl::class,
                ],
                'factories' => [
                    AuthenticationService::class          => AuthenticationServiceFactory::class,
                    Handler\HomePageHandler::class => Handler\HomePageHandlerFactory::class,
//                    JwtAuthenticationMiddleware::class       => JwtAuthenticationMiddlewareFactory::class,
//                    JwtRecentAuthenticationMiddleware::class => JwtRecentAuthenticationMiddlewareFactory::class,
                    UserInterface::class                     => UserFactory::class,
                    ZendAuthentication::class                => ZendAuthenticationFactory::class,


                    AuthenticationMiddleware::class => AuthenticationMiddlewareFactory::class,
                    ZendAuthenticationService::class      => ZendAuthenticationServiceFactory::class,
                ],
                'invokables' => [
                    Handler\PingHandler::class => Handler\PingHandler::class,
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
