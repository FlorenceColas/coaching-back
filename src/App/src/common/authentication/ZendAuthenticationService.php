<?php

namespace App\Common\Authentication;

use Zend\Authentication\Adapter\AdapterInterface;
use Zend\Authentication\AuthenticationService;
use Zend\Authentication\Adapter\DbTable\CredentialTreatmentAdapter;
use Zend\Authentication\Storage\Session;
use Zend\Db\Adapter\Adapter as DbAdapter;
use Zend\Db\Sql\Join;
use Zend\Db\Sql\Sql;

class ZendAuthenticationService extends AuthenticationService implements AdapterInterface
{
    const ROLE_ADMIN_WAREHOUSE = 'admin_warehouse';
    const ROLE_GUEST_WAREHOUSE = 'guest_warehouse';

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
    }

    public function authenticateUser($logon, $password)
    {
        if (empty($logon) or empty($password)) {
            return [
                'valid'  => false,
                'result' => [
                    'code'    => 999,
                    'message' => ['The username and password are mandatory'],
                ],
            ];
        }

        $this->getAdapter()
            ->setIdentity($logon)
            ->setCredential($password);

        $result = $this->authenticate();

        if ($result->isValid()) {
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
                ->where(['username' => $logon]);

            $statement = $sql->prepareStatementForSqlObject($select);
            $userRoles = iterator_to_array($statement->execute());

            $roles = [];
            foreach ($userRoles as $role) {
                $roles[] = $role['key_label'];
            }

            $userJwt = new User(
                $this->config,
                $logon,
                [
                    'name' => $userRoles[0]['displayname'],
                    'uid'  => $userRoles[0]['id'],
                ],
                $roles
            );

            $userJwt->createJwtRecord($this->authAdapter);

            $update = $sql->update('users')
                ->set(['lastconnection' => (new \DateTime())->format('Y-m-d H:i:s')])
                ->where(['username' => $logon]);

            $statement = $sql->buildSqlString($update);
            $this->authAdapter->query($statement, DbAdapter::QUERY_MODE_EXECUTE);

            return [
                'valid'   => true,
                'result' => [
                    'code'    => $result->getCode(),
                    'message' => $result->getMessages(),
                ],
            ];
        } else {
            return [
                'valid'  => false,
                'result' => [
                    'code'    => $result->getCode(),
                    'message' => $result->getMessages(),
                ],
            ];
        }
    }

    public function sessionIsValid()
    {
        return [true, []];
    }

    public function simulateAuthenticateUser($username)
    {
        $sql = new Sql($this->authAdapter);
        $select = $sql->select()
            ->columns(['password'])
            ->from('users')
            ->where(['username' => $username]);

        $statement = $sql->prepareStatementForSqlObject($select);
        $user      = iterator_to_array($statement->execute());
        $password  = $user[0]['password'] ?? '';

        $this->getAdapter()
            ->setIdentity($username)
            ->setCredential($password);

        $result = $this->authenticate();

        return $result->isValid();
    }
}
