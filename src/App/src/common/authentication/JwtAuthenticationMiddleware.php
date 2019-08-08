<?php

declare(strict_types=1);

namespace App\Common\Authentication;

use Firebase\JWT\ExpiredException;
use Firebase\JWT\JWT;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Zend\Expressive\Authentication\UserInterface;

class JwtAuthenticationMiddleware implements MiddlewareInterface
{
    protected $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $header = $request->getHeader('Authorization')[0] ?? null;

        if (!preg_match('/Bearer (?P<jwt>.*)/', $header, $matches)) {
            return $handler->handle($request);
        }

        try {
            $user = JWT::decode(
                $matches['jwt'],
                $this->config['jwt']['key'],
                $this->config['jwt']['allowed_algs']
            );
        } catch (\Exception $e) {
            if (get_class($e) === ExpiredException::class) {
                return $handler->handle($request);
            }
        }

        $identity = new User(
            $this->config,
            $user->identity,
            (array) $user->roles,
            (array) $user->details + ['exp' => $user->exp]
        );

        return $handler->handle($request->withAttribute(UserInterface::class, $identity));
    }
}
