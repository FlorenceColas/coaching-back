<?php

declare(strict_types=1);

namespace App\Common\Middleware;

use Fig\Http\Message\RequestMethodInterface as RequestMethod;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Some proxies or company policies don't allow HTTP verbs other than GET and POST.
 * Web applications, especially REST APIs need to use all the HTTP verbs available.
 *
 * This middleware overrides the HTTP method in request with the one provided in query
 * string parameter "_method".
 */
class OverrideHttpMethodMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if ($request->getMethod() === RequestMethod::METHOD_POST) {
            $queryParams = $request->getQueryParams();

            if (!empty($queryParams['_method'])) {
                $method = strtoupper($queryParams['_method']);

                switch ($method) {
                    case RequestMethod::METHOD_DELETE:
                    case RequestMethod::METHOD_PATCH:
                    case RequestMethod::METHOD_PUT:
                        // Further code should not be aware of this trick.
                        unset($queryParams['_method']);

                        $request = $request->withMethod($method)
                            ->withQueryParams($queryParams);

                        return $handler->handle($request);
                }
            }
        }

        return $handler->handle($request);
    }
}
