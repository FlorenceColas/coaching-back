<?php

namespace App\Common\Authentication;

use Zend\Authentication\Adapter\AdapterInterface;
use Zend\Authentication\AuthenticationService as ZendAuthenticationService;
use Zend\Authentication\Adapter\DbTable\CredentialTreatmentAdapter;
use Zend\Db\Adapter\Adapter as DbAdapter;
use Zend\Db\Sql\Join;
use Zend\Db\Sql\Sql;

class AuthenticationService extends ZendAuthenticationService implements AdapterInterface
{
    const ROLE_COACH   = 'coach';
    const ROLE_ATHLETE = 'athelete';

    const USER_STATUS_BLOCKED  = 2;
    const USER_STATUS_DISABLED = 0;
    const USER_STATUS_ENABLED  = 1;

    protected $authAdapter;
    protected $config;

    public function __construct (array $config)
    {
        $this->config = $config;

        $this->authAdapter = new DbAdapter([
            'database'       => $config['db_auth']['database'],
            'driver'         => $config['db_auth']['driver'],
            'driver_options' => $config['db_auth']['driver_options'],
            'hostname'       => $config['db_auth']['hostname'],
            'password'       => $config['db_auth']['password'],
            'username'       => $config['db_auth']['username'],
        ]);

        $dbTableAuthAdapter = new CredentialTreatmentAdapter(
            $this->authAdapter,
            'users',
            'username',
            'password',
            'SHA2(CONCAT(salt,?), 512)'
        );
        $dbTableAuthAdapter->getDbSelect()->where('status = ' . self::USER_STATUS_ENABLED);

        $this->setAdapter($dbTableAuthAdapter);
    }

    public function authenticateUser(string $username, string $password): User
    {
        $this->getAdapter()
            ->setIdentity($username)
            ->setCredential($password);

        $result = $this->authenticate();

        if (!$result->isValid()) {
            return null;
        } else {
            $sql = new Sql($this->authAdapter);
            $select = $sql->select()
                ->columns(
                    [
                        'id',
                        'username',
                        'displayname',
                    ]
                )
                ->from(['A' => 'users'])
                ->join(
                    ['B' => 'users_roles'],
                    'B.user = A.id',
                    [],
                    Join::JOIN_LEFT
                )
                ->join(
                    ['C' => 'roles'],
                    'C.id = B.role',
                    ['key_label'],
                    Join::JOIN_LEFT
                )
                ->where(['username' => $username]);

            $statement = $sql->prepareStatementForSqlObject($select);
            $userRoles = iterator_to_array($statement->execute());

            $roles = [];
            foreach ($userRoles as $role) {
                $roles[] = $role['key_label'];
            }

            $userJwt = new User(
                $this->config,
                $username,
                [
                    'name' => $userRoles[0]['displayname'],
                    'uid'  => $userRoles[0]['id'],
                ],
                $roles
            );

            $userJwt->createJwtRecord($this->authAdapter);

            $update = $sql->update('users')
                ->set(['lastconnection' => (new \DateTime())->format('Y-m-d H:i:s')])
                ->where(['username' => $username]);

            $statement = $sql->buildSqlString($update);
            $this->authAdapter->query($statement, DbAdapter::QUERY_MODE_EXECUTE);

            return $userJwt;
        }
    }
}
