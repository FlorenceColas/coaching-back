<?php

declare(strict_types=1);

namespace App\Model;

use App\Entity\Activity as ActivityEntity;
use Zend\Db\Adapter\AdapterInterface as DbAdapterInterface;
use Zend\Db\Sql\Predicate\Expression;
use Zend\Db\Sql\Sql;
use Zend\EventManager\EventManagerAwareTrait;
use Zend\Stdlib\Parameters;
use Zend\Stdlib\ParametersInterface;

class Activities
    extends AbstractModel
{
    use EventManagerAwareTrait;

    /**
     * @var DbAdapterInterface
     */
    protected $dbAdapter;
    protected $categories = [
        'off'     => 1,
        'swim'    => 2,
        'bike'    => 3,
        'run'     => 4,
        'fitness' => 5,
        'race'    => 6,
    ];
    protected static $sets = [
        'default' => [
            'fields' => [
                'athlete',
                'week',
                'year',
            ],
        ],
    ];

    public function __construct(DbAdapterInterface $dbAdapter)
    {
        $this->dbAdapter = $dbAdapter;
    }

    public function createActivity(array $data)
    {
        $sql = new Sql($this->dbAdapter);

        $insert = $sql->insert('activities')
            ->columns([
                'athletes_users_id',
                'categories_id',
                'types_id',
                'activity_date',
                'planned',
                'planned_content',
                'planned_distance',
                'planned_time',
            ])
            ->values([
                $data['athleteUserId'],
                $this->categories[$data['categoryId']],
                $data['activityType'],
                date('Y-m-d',(int)round($data['activityDay'] / 1000, 0)),
                $data['planned'],
                $data['plannedContent'],
                $data['plannedDistance'],
                $data['plannedTime'],
/*                $data['realisedContent'],
                $data['realisedDistance'],
                $data['realisedTime'],
                $data['state'],
*/
            ]);

        $insert = $sql->buildSqlString($insert);

        $pdo = $this->dbAdapter
            ->getDriver()
            ->getConnection()
            ->getResource();

        $pdo->exec($insert);
        $key = $pdo->lastInsertId();

        return $key;
    }

    public function deleteActivity(string $id)
    {
        $sql = new Sql($this->dbAdapter);
        $delete = $sql->delete()
            ->from('activities')
            ->where(['id' => $id]);

        $delete = $sql->buildSqlString($delete);

        $pdo = $this->dbAdapter
            ->getDriver()
            ->getConnection()
            ->getResource();

        $res = $pdo->exec($delete);

        return (bool) $res;
    }

    public function getActivity(string $key, ParametersInterface $params = null): ActivityEntity
    {
        $params or $params = new Parameters();
        $params = self::processParams($params);

        $sql = new Sql($this->dbAdapter);
        $select = $sql->select()
            ->columns($params['fields'])
            ->from('')
            ->where([])
            ->order('');

        $select = $sql->buildSqlString($select);

        $pdo = $this->dbAdapter
            ->getDriver()
            ->getConnection()
            ->getResource();

        $stmt = $pdo->query(
            $select,
            \PDO::FETCH_CLASS | \PDO::FETCH_PROPS_LATE,
            ActivityEntity::class
        );
        $entity = $stmt->fetch();

        $stmt->closeCursor();

        return $entity ?: null;
    }

    public function getActivities(ParametersInterface $params = null): array
    {
        $params or $params = new Parameters();
        $params = self::processParams($params);

        $dto = new \DateTime();
        $dto->setISODate((int)$params['year'], (int)$params['week']);

        $weekStart = $dto->format('Y-m-d');
        $dto->modify('+6 days');
        $weekEnd = $dto->format('Y-m-d');

        $activitiesResult = [];

        $sql = new Sql($this->dbAdapter);
        $select = $sql->select()
            ->columns(
                [
                    'id',
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
            ->join(['B' => 'categories'],
                'B.id = A.categories_id',
                ['name']
            )
            ->where([
                'athletes_users_id' => (int)$params['athlete'],
                new Expression("activity_date >= ?",  $weekStart),
                new Expression("activity_date <= ?",  $weekEnd),
            ]);

        $statement = $sql->prepareStatementForSqlObject($select);
        $activities = iterator_to_array($statement->execute());

        foreach ($activities as $activity) {
            $day = date('N', strtotime($activity['activity_date']));
            $activitiesResult[] = [
                'id'               => $activity['id'],
                'athleteUserId'    => $params['athlete'],
                'categoryId'       => $activity['name'],
                'typeId'           => $activity['types_id'],
                'activityDay'      => strtotime($activity['activity_date']) * 1000,
                'dayOfWeek'        => $day,
                'planned'          => $activity['planned'],
                'plannedContent'   => $activity['planned_content'],
                'plannedDistance'  => $activity['planned_distance'],
                'plannedTime'      => $activity['planned_time'],
                'realisedContent'  => $activity['realised_content'],
                'realisedDistance' => $activity['realised_distance'],
                'realisedTime'     => $activity['realised_time'],
                'state'            => $activity['state'],
            ];
        }

        return $activitiesResult;
    }

    public function updateActivity(string $key, iterable $data)
    {
        $data = array_intersect_key(
            (array)$data,
            array_flip([
                'value',
            ])
        );

        $sql = new Sql($this->dbAdapter);
        $update = $sql->update('config')
            ->set(['value' => $data['value']])
            ->where(['key' => $key]);

        $update = $sql->buildSqlString($update);

        $pdo = $this->dbAdapter
            ->getDriver()
            ->getConnection()
            ->getResource();

        $ret = $pdo->exec($update);

        return (bool) $ret;
    }
}
