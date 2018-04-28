<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftRouter;

use FastRoute\Dispatcher;
use RuntimeException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

function handle(Dispatcher $dispatcher, Request $request, string $prefix = '') : Response
{
    $uri = str_replace('//', '/', ('/' . parse_url($request->getUri(), PHP_URL_PATH)));

    if ('' !== $prefix) {
        $uri = preg_replace('/^' . preg_quote($prefix, '/') . '/', '', $uri);
    }

    $routeInfo = $dispatcher->dispatch($request->getMethod(), $uri);

    if (Dispatcher::NOT_FOUND === $routeInfo[0]) {
        return new Response('404 Not Found', Response::HTTP_NOT_FOUND, [
            'content-type' => 'text/plain',
        ]);
    } elseif (Dispatcher::METHOD_NOT_ALLOWED === $routeInfo[0]) {
        return new Response('Method Not Allowed', Response::HTTP_METHOD_NOT_ALLOWED, [
            'content-type' => 'text/plain',
        ]);
    } elseif (Dispatcher::FOUND !== $routeInfo[0]) {
        return new Response('Unknown error', Response::HTTP_INTERNAL_SERVER_ERROR, [
            'content-type' => 'text/plain',
        ]);
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
        throw new RuntimeException(
            'Dispatcher generated a found response without a route handler!'
        );
    }

    return $route::DaftRouterHandleRequest($request, (array) ($routeInfo[2] ?? []));
}
