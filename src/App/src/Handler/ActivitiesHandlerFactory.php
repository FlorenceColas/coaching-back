<?php

declare(strict_types=1);

namespace App\Handler;

use App\Model\Activities;
use Psr\Container\ContainerInterface;
use Psr\Http\Server\RequestHandlerInterface;

class ActivitiesHandlerFactory
{
    public function __invoke(ContainerInterface $container) : RequestHandlerInterface
    {
        $config = $container->get('config');
        $model  = $container->get(Activities::class);

        return new ActivitiesHandler($config, $model);
    }
}
