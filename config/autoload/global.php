<?php

declare(strict_types=1);

use Zend\ConfigAggregator\ConfigAggregator;

return [
    // Toggle the configuration cache. Set this to boolean false, or remove the
    // directive, to disable configuration caching. Toggling development mode
    // will also disable it by default; clear the configuration cache using
    // `composer clear-config-cache`.
    ConfigAggregator::ENABLE_CACHE => true,

    'db' => [
        'driver'         => 'pdo_mysql',
        'driver_options' => [
            \PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES UTF8",
        ],
    ],

    'db_auth' => [
        'driver'         => 'pdo_mysql',
        'driver_options' => [
            \PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES UTF8",
        ],
    ],

    // Enable debugging; typically used to provide debugging information within templates.
    'debug' => false,

    // Provides application-wide services.
    // We recommend using fully-qualified class names whenever possible as
    // service names.
    'dependencies' => [
        // Use 'aliases' to alias a service name to another service. The
        // key is the alias name, the value is the service to which it points.
        'aliases' => [
            // Fully\Qualified\ClassOrInterfaceName::class => Fully\Qualified\ClassName::class,
        ],
        // Use 'invokables' for constructor-less services, or services that do
        // not require arguments to the constructor. Map a service name to the
        // class name.
        'invokables' => [
            // Fully\Qualified\InterfaceName::class => Fully\Qualified\ClassName::class,
        ],
        // Use 'factories' for services provided by callbacks/factory classes.
        'factories'  => [
            // Fully\Qualified\ClassName::class => Fully\Qualified\FactoryName::class,
        ],
    ],

    'jwt' => [
        'expiration' => 900, //15'
    ],

    'zend-expressive' => [
        // Provide templates for the error handling middleware to use when
        // generating responses.
        'error_handler' => [
            'template_404'   => 'error::404',
            'template_error' => 'error::error',
        ],
    ],
];
