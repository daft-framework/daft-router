<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftRouter\Router;

use FastRoute\Dispatcher\GroupCountBased as Base;
use SignpostMarv\DaftRouter\ResponseException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class Dispatcher extends Base
{
    final public function dispatch($httpMethod, $uri)
    {
        $routeInfo = parent::dispatch($httpMethod, $uri);

        if (Dispatcher::NOT_FOUND === $routeInfo[0]) {
            throw new ResponseException('Dispatcher was not able to generate a response!', 404);
        } elseif (Dispatcher::METHOD_NOT_ALLOWED === $routeInfo[0]) {
            throw new ResponseException('Dispatcher was not able to generate a response!', 405);
        }

        return $routeInfo;
    }

    public function handle(Request $request, string $prefix = '') : Response
    {
        $regex = '/^' . preg_quote($prefix, '/') . '/';
        $path = preg_replace($regex, '', (string) parse_url($request->getUri(), PHP_URL_PATH));
        $routeInfo = $this->dispatch($request->getMethod(), str_replace('//', '/', ('/' . $path)));

        /**
        * @var string[] $middlewares
        */
        $middlewares = $routeInfo[1];
        $route = array_pop($middlewares);

        $resp = $this->RunMiddlewareFirstPass($request, ...((array) $middlewares));

        if ( ! ($resp instanceof Response)) {
            /**
            * @var Response $resp
            */
            $resp = $route::DaftRouterHandleRequest($request, $routeInfo[2]);

            $resp = $this->RunMiddlewareSecondPass($request, $resp, ...$middlewares);
        }

        return $resp;
    }

    private function RunMiddlewareFirstPass(Request $request, string ...$middlewares) : ? Response
    {
        $response = null;

        /**
        * @var string $middleware
        */
        foreach ($middlewares as $middleware) {
            /**
            * @var Response|null $response
            */
            $response = $middleware::DaftRouterMiddlewareHandler($request, $response);
        }

        return $response;
    }

    private function RunMiddlewareSecondPass(
        Request $request,
        Response $response,
        string ...$middlewares
    ) : Response {
        /**
        * @var string $middleware
        */
        foreach ($middlewares as $middleware) {
            /**
            * @var Response $response
            */
            $response = $middleware::DaftRouterMiddlewareHandler($request, $response);
        }

        return $response;
    }
}
