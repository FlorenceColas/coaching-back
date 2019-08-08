<?php

declare(strict_types=1);

namespace App\Common\Authentication;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Zend\Diactoros\Response\JsonResponse;
use Zend\Expressive\Authentication\AuthenticationInterface;

class AuthenticationMiddleware implements MiddlewareInterface
{
    /**
     * @var AuthenticationInterface
     */
    protected $authService;

    public function __construct(ZendAuthenticationService $authService)
    {
        $this->authService = $authService;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler) : ResponseInterface
    {
        $body     = $request->getParsedBody();
        $username = $body['username'];
        $password = $body['password'];

        if (!empty($username) && !empty($password)) {
            //check user and get associated jwt
            return $handler->handle($request);
        } else {
            return new JsonResponse('not authorized', 401);
        }
    }
}
