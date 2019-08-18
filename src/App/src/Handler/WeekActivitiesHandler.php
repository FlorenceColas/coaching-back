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

class WeekActivitiesHandler implements RequestHandlerInterface
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
                'athlete',
                'week',
                'year',
            ])
        ));

        $dto = new \DateTime();
        $dto->setISODate((int)$args['year'], (int)$args['week']);

        $weekStart = $dto->format('Y-m-d');
        $dto->modify('+6 days');
        $weekEnd = $dto->format('Y-m-d');

        $activitiesResult = [];

        $sql = new Sql($this->dbAdapter);
        $select = $sql->select()
            ->columns(
                [
                    'id',
                    'categories_id',
                    'types_id',
                    'activity_date',
                    'planned',
                    'planned_content',
                    'planned_distance',
                    'planned_time',
                    'realised_content',
                    'realised_distance',
                    'realised_time',
                    'state',
                ]
            )
            ->from(['A' => 'activities'])
            ->where([
                'athletes_users_id' => $args['athlete'],
                new Expression("activity_date >= '$weekStart' AND activity_date <= '$weekEnd'")
            ]);

        $statement = $sql->prepareStatementForSqlObject($select);
        $activities = iterator_to_array($statement->execute());

        foreach ($activities as $activity) {
            $day = date('N', strtotime($activity['activity_date']));
            $activitiesResult[] = [
                'id' => $activity['id'],
                'athleteUserId' => $args['athlete'],
                'categoryId' => $activity['categories_id'],
                'typeId' => $activity['types_id'],
                'activityDay' => strtotime($activity['activity_date']) * 1000,
                'dayOfWeek' => $day,
                'plannedContent' => $activity['planned_content'],
                'plannedDistance' => $activity['planned_distance'],
                'plannedTime' => $activity['planned_time'],
                'realisedContent' => $activity['realised_content'],
                'realisedDistance' => $activity['realised_distance'],
                'realisedTime' => $activity['realised_time'],
                'state' => $activity['state'],
            ];
        }

        return new JsonResponse($activitiesResult, StatusCodeInterface::STATUS_OK);
    }
}
