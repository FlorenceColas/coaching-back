<?php

declare(strict_types=1);

namespace App\Model;

use Psr\Container\ContainerInterface;
use Zend\Db\Adapter\AdapterInterface as DbAdapterInterface;

class AthletesFactory
{
    public function __invoke(ContainerInterface $container): Athletes
    {
        $dbAdapter = $container->get(DbAdapterInterface::class);

        $model = new Athletes($dbAdapter);

        return $model;
    }
}
