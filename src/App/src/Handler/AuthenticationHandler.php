<?php
declare(strict_types=1);

namespace App\Handler;

use Fig\Http\Message\StatusCodeInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\JsonResponse;
use Zend\Stdlib\Parameters;

class AuthenticationHandler implements RequestHandlerInterface
{
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



        $json = new JsonResponse(
            ['jwt' => '$user->getJwt()'],

            StatusCodeInterface::STATUS_OK
        );

        return $json;
    }
}
