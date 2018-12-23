<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftRouter\Router;

use FastRoute\Dispatcher\GroupCountBased as Base;
use SignpostMarv\DaftRouter\DaftRequestInterceptor;
use SignpostMarv\DaftRouter\DaftResponseModifier;
use SignpostMarv\DaftRouter\ResponseException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class Dispatcher extends Base
{
    /**
    * @param string $httpMethod
    * @param string $uri
    *
    * @return array
    */
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

    /**
    * @psalm-suppress InvalidStringClass
    */
    public function handle(Request $request, string $prefix = '') : Response
    {
        $regex = '/^' . preg_quote($prefix, '/') . '/';
        $path = preg_replace($regex, '', (string) parse_url($request->getUri(), PHP_URL_PATH));

        /**
        * @var array{1:array}
        */
        $routeInfo = $this->dispatch($request->getMethod(), str_replace('//', '/', ('/' . $path)));
        $route = (string) array_pop($routeInfo[1]);

        $firstPass = [];
        $secondPass = [];

        foreach (array_map('strval', (array) $routeInfo[1]) as $middleware) {
            if (is_a($middleware, DaftRequestInterceptor::class, true)) {
                $firstPass[] = $middleware;
            }

            if (is_a($middleware, DaftResponseModifier::class, true)) {
                $secondPass[] = $middleware;
            }
        }

        $resp = $this->RunMiddlewareFirstPass($request, ...$firstPass);

        if ( ! ($resp instanceof Response)) {
            /**
            * @var Response
            */
            $resp = $route::DaftRouterHandleRequest($request, $routeInfo[2]);
        }

        $resp = $this->RunMiddlewareSecondPass($request, $resp, ...$secondPass);

        return $resp;
    }

    /**
    * @psalm-suppress InvalidStringClass
    */
    private function RunMiddlewareFirstPass(Request $request, string ...$middlewares) : ? Response
    {
        $response = null;

        foreach ($middlewares as $middleware) {
            /**
            * @var Response|null
            */
            $response = $middleware::DaftRouterMiddlewareHandler($request, $response);
        }

        return $response;
    }

    /**
    * @psalm-suppress InvalidStringClass
    */
    private function RunMiddlewareSecondPass(
        Request $request,
        Response $response,
        string ...$middlewares
    ) : Response {
        foreach ($middlewares as $middleware) {
            /**
            * @var Response
            */
            $response = $middleware::DaftRouterMiddlewareModifier($request, $response);
        }

        return $response;
    }
}
