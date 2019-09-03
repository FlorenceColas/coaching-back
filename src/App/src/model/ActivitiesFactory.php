<?php

declare(strict_types=1);

namespace App\Model;

use Psr\Container\ContainerInterface;
use Zend\Db\Adapter\AdapterInterface as DbAdapterInterface;

class ActivitiesFactory
{
    public function __invoke(ContainerInterface $container): Activities
    {
        $dbAdapter = $container->get(DbAdapterInterface::class);

        $model = new Activities($dbAdapter);

        return $model;
    }
}
