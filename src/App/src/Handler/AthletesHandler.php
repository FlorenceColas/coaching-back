<?php
declare(strict_types=1);

namespace App\Handler;

use Fig\Http\Message\StatusCodeInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Db\Adapter\Adapter as DbAdapter;
use Zend\Db\Sql\Predicate\Expression;
use Zend\Db\Sql\Sql;
use Zend\Diactoros\Response\JsonResponse;
use Zend\Stdlib\Parameters;

class AthletesHandler implements RequestHandlerInterface
{
    protected $config;
    protected $dbAdapter;

    public function __construct (array $config)
    {
        $this->config = $config;

        $this->dbAdapter = new DbAdapter([
            'database'       => $config['db']['database'],
            'driver'         => $config['db']['driver'],
            'driver_options' => $config['db']['driver_options'],
            'hostname'       => $config['db']['hostname'],
            'password'       => $config['db']['password'],
            'username'       => $config['db']['username'],
        ]);

    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $args = new Parameters(array_intersect_key(
            $request->getAttributes(),
            array_flip([
                'coach',
            ])
        ));

        $sql = new Sql($this->dbAdapter);
        $select = $sql->select()
            ->columns(
                [
                    'id' => 'users_id',
                    'name',
                ]
            )
            ->from(['A' => 'athletes'])
            ->join(
                ['B' => 'rel_coach_athletes'],
                'B.athletes_users_id = A.users_id',
                []
            )
            ->where([
                'coach_users_id' => (int)$args['coach']
            ]);

        $statement = $sql->prepareStatementForSqlObject($select);
        $str = $sql->buildSqlString($select);
        $activities = iterator_to_array($statement->execute());

        return new JsonResponse($activities, StatusCodeInterface::STATUS_OK);
    }
}
