<?php
declare(strict_types=1);

namespace App\Handler;

use App\Common\Authentication\User;
use Fig\Http\Message\StatusCodeInterface;
use Firebase\JWT\JWT;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\JsonResponse;

class RefreshTokenHandler implements RequestHandlerInterface
{
    protected $config;

    public function __construct (array $config)
    {
        $this->config = $config;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $header = $request->getHeader('Authorization')[0] ?? null;

        if (!preg_match('/Bearer (?P<jwt>.*)/', $header, $matches)) {
            return new JsonResponse(
                'No token to refresh',
                StatusCodeInterface::STATUS_UNAUTHORIZED
            );
        }

        try {
            $user = JWT::decode(
                $matches['jwt'],
                $this->config['jwt']['key'],
                $this->config['jwt']['allowed_algs']
            );
        } catch (\Exception $e) {
            return new JsonResponse(
                'Invalid token',
                StatusCodeInterface::STATUS_FORBIDDEN
            );
        }

        if ($user) {
            $newUser = new User(
                $this->config,
                $user->username,
                (array)$user->details,
                (array)$user->roles
            );
            return new JsonResponse(
                $newUser->getJwt(),
                StatusCodeInterface::STATUS_OK
            );
        } else {
            return new JsonResponse(
                'Invalid token',
                StatusCodeInterface::STATUS_FORBIDDEN
            );
        }
    }
}
