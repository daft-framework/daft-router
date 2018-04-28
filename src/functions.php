<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftRouter;

use FastRoute\Dispatcher;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

function handle(Dispatcher $dispatcher, Request $request, string $prefix = '') : Response
{
    $uri = str_replace(
        '//',
        '/',
        '/' . preg_replace(
            ('/^' . preg_quote($prefix, '/') . '/'),
            '',
            parse_url($request->getUri(), PHP_URL_PATH)
        )
    );

    $routeInfo = $dispatcher->dispatch($request->getMethod(), $uri);

    if (Dispatcher::NOT_FOUND === $routeInfo[0]) {
        throw new ResponseException('Dispatcher was not able to generate a response!', 404);
    } elseif (Dispatcher::METHOD_NOT_ALLOWED === $routeInfo[0]) {
        throw new ResponseException('Dispatcher was not able to generate a response!', 405);
    } elseif (Dispatcher::FOUND !== $routeInfo[0]) {
        throw new ResponseException('Dispatcher generated an unsupported response!', 500);
    }

    $middlewares = array_values((array) ($routeInfo[1] ?? []));
    $route = array_pop($middlewares);

    $resp = null;

    foreach ($middlewares as $middleware) {
        $resp = $middleware::DaftRouterMiddlewareHandler($request, $resp);
    }

    if ($resp instanceof Response) {
        return $resp;
    } elseif ( ! is_a($route, DaftRoute::class, true)) {
        throw new ResponseException(
            'Dispatcher generated a found response without a route handler!',
            500
        );
    }

    return $route::DaftRouterHandleRequest($request, (array) ($routeInfo[2] ?? []));
}
