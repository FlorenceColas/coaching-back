<?php

declare(strict_types=1);

namespace App\Model;

use App\Entity\Athlete as AthleteEntity;
use Zend\Db\Adapter\AdapterInterface as DbAdapterInterface;
use Zend\Db\Sql\Sql;
use Zend\EventManager\EventManagerAwareTrait;
use Zend\Stdlib\Parameters;
use Zend\Stdlib\ParametersInterface;

class Athletes
    extends AbstractModel
{
    use EventManagerAwareTrait;

    /**
     * @var DbAdapterInterface
     */
    protected $dbAdapter;
    protected static $sets = [
        'default' => [
            'fields' => [
                'id',
                'name',
            ],
        ],
    ];

    public function __construct(DbAdapterInterface $dbAdapter)
    {
        $this->dbAdapter = $dbAdapter;
    }

    public function createAthlete(array $data)
    {
        $data = array_intersect_key(
            (array)$data,
            array_flip([
                'name',
            ])
        );

        $sql = new Sql($this->dbAdapter);

        $insert = $sql->insert('athletes')
            ->values($data);

        $insert = $sql->buildSqlString($insert);

        $pdo = $this->dbAdapter
            ->getDriver()
            ->getConnection()
            ->getResource();

        $pdo->exec($insert);
        $key = $pdo->lastInsertId();

        return $key;
    }

    public function deleteAthlete(string $id)
    {
        $sql = new Sql($this->dbAdapter);
        $delete = $sql->delete()
            ->from('athletes')
            ->where(['id' => $id]);

        $delete = $sql->buildSqlString($delete);

        $pdo = $this->dbAdapter
            ->getDriver()
            ->getConnection()
            ->getResource();

        $res = $pdo->exec($delete);

        return (bool) $res;
    }

    public function getAthlete(string $key, ParametersInterface $params = null): AthleteEntity
    {
        $sql = new Sql($this->dbAdapter);
        $select = $sql->select()
            ->columns($params['fields'])
            ->from(['A' => 'athletes'])
            ->where([
                'id' => $key
            ]);

        $select = $sql->buildSqlString($select);

        $pdo = $this->dbAdapter
            ->getDriver()
            ->getConnection()
            ->getResource();

        $stmt = $pdo->query(
            $select,
            \PDO::FETCH_CLASS | \PDO::FETCH_PROPS_LATE,
            AthleteEntity::class
        );
        $entity = $stmt->fetch();

        $stmt->closeCursor();

        return $entity ?: null;
    }

    public function getAthletes(ParametersInterface $params = null): array
    {
        $params or $params = new Parameters();
        $params = self::processParams($params);

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
                'coach_users_id' => (int)$params['coach']
            ]);

        $pdo = $this->dbAdapter
            ->getDriver()
            ->getConnection()
            ->getResource();

        $stmt = $pdo->query($select, \PDO::FETCH_OBJ);
        $collection = $stmt->fetchAll();

        return $collection;
    }

    public function updateAthlete(string $id, array $data): bool
    {
        $data = array_intersect_key(
            (array)$data,
            array_flip([
                'name',
            ])
        );

        $sql = new Sql($this->dbAdapter);
        $update = $sql->update('athletes')
            ->set($data)
            ->where(['id' => $id]);

        $update = $sql->buildSqlString($update);

        $pdo = $this->dbAdapter
            ->getDriver()
            ->getConnection()
            ->getResource();

        $ret = $pdo->exec($update);

        return (bool) $ret;
    }
}
