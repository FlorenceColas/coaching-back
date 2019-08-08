<?php

declare(strict_types=1);

namespace App\Common\Authentication;;

use Firebase\JWT\JWT;
use Zend\Db\Adapter\Adapter as DbAdapter;
use Zend\Db\Sql\Sql;
use Zend\Expressive\Authentication\UserInterface;

final class User implements UserInterface
{
    const JWT_STATUS_ENABLED = 1;
    const JWT_STATUS_BLOCKED = 2;

    private $config;
    private $details;
    private $exp;
    private $iat;
    private $roles;
    private $secretKey;
    private $username;

    public function __construct(
        array $config,
        string $usernameOrJwt,
        array $details,
        array $roles = []
    )
    {
        $this->config   = $config;
        $this->iat      = time();
        $this->exp      = $this->iat + $this->config['jwt']['expiration'];
        $this->username = $usernameOrJwt;
        $this->roles    = $roles;
        $this->details  = [
            'name' => $details['name'] ?? '',
            'uid'  => $details['uid'] ?? '',
        ];
    }

    public function createJwtRecord(DbAdapter $authAdapter)
    {
        $this->secretKey = $this->getRandomSalt();
        $jwt = $this->getJwt();

        $sql = new Sql($authAdapter);

        $insert = $sql->insert('jwt')
            ->columns([
                'jwt',
                'secret_key',
                'status',
            ])
            ->values([
                $jwt,
                $this->secretKey,
                self::JWT_STATUS_ENABLED
            ]);

        $insert = $sql->buildSqlString($insert);
        $authAdapter->query($insert, DbAdapter::QUERY_MODE_EXECUTE);
    }

    public function getDetail(string $name, $default = null)
    {
        return $this->details[$name] ?? $default;
    }

    public function getDetails() : array
    {
        return $this->details;
    }

    public function getExp(): int
    {
        return $this->exp;
    }

    public function getIdentity() : string
    {
        return $this->username;
    }

    public function getJwt(): string
    {
        $jwt = JWT::encode(
            [
                'iat'      => $this->iat,
                'exp'      => $this->exp,
                'username' => $this->username,
                'roles'    => $this->roles,
                'details'  => $this->details,
            ],
            $this->secretKey,
            $this->config['jwt']['allowed_algs'][0]
        );

        return $jwt;
    }

    protected function getRandomSalt(): string
    {
        $strong = true;
        $bytes = openssl_random_pseudo_bytes(256, $strong);

        return  bin2hex($bytes);
    }

    public function getRoles(): array
    {
        return $this->roles;
    }
}
