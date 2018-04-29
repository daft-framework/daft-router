<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftRouter;

use SignpostMarv\DaftRouter\Router\Dispatcher;
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

    $middlewares = $routeInfo[1];
    $route = array_pop($middlewares);

    $resp = null;

    foreach ($middlewares as $middleware) {
        $resp = $middleware::DaftRouterMiddlewareHandler($request, $resp);
    }

    if ($resp instanceof Response) {
        return $resp;
    }

    return $route::DaftRouterHandleRequest($request, (array) ($routeInfo[2] ?? []));
}
