<?php
declare(strict_types=1);

namespace App\Handler;

use App\Common\Authentication\AuthenticationService;
use Fig\Http\Message\StatusCodeInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\JsonResponse;
use Zend\Stdlib\Parameters;

class AuthenticationHandler implements RequestHandlerInterface
{
    private $authService;

    public function __construct(AuthenticationService $authService) {
        $this->authService = $authService;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        // Filtering console arguments out from POST query.
        $args = new Parameters(array_intersect_key(
            $request->getParsedBody(),
            array_flip([
                'username',
                'password',
            ])
        ));

        if (empty($args['username']) or empty($args['password'])) {
            $json = new JsonResponse(
                ['Incorrect credentials provided', $args['username']],
                StatusCodeInterface::STATUS_UNAUTHORIZED
            );
        } else {
            $user = $this->authService->authenticateUser($args['username'], $args['password']);

            if ($user) {
                $json = new JsonResponse(
                    $user->getJwt(),
                    StatusCodeInterface::STATUS_OK
                );
            } else {
                $json = new JsonResponse(
                    ['Supplied credentials are invalid'],
                    StatusCodeInterface::STATUS_UNAUTHORIZED
                );
            }
        }

        return $json;
    }
}
