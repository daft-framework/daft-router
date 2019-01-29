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
    const INT_ARRAY_INDEX_HTTP_METHOD = 2;

    const INT_ARRAY_INDEX_ROUTE_ARGS = 3;

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

    public function handle(Request $request, string $prefix = '') : Response
    {
        $regex = '/^' . preg_quote($prefix, '/') . '/';
        $path = preg_replace($regex, '', parse_url($request->getUri(), PHP_URL_PATH) ?? '');

        /**
        * @var array{1:array, 2:string, 3:array<string, string>}
        */
        $routeInfo = $this->dispatch($request->getMethod(), str_replace('//', '/', ('/' . $path)));

        $routeArgs = [];

        if (isset($routeInfo[self::INT_ARRAY_INDEX_ROUTE_ARGS])) {
            $routeArgs = $routeInfo[self::INT_ARRAY_INDEX_ROUTE_ARGS];
        }

        /**
        * @psalm-var \SignpostMarv\DaftRouter\DaftRoute
        */
        $route = array_pop($routeInfo[1]) ?: '';

        /**
        * @psalm-var array<int, class-string<DaftRequestInterceptor>>
        *
        * @var array<int, string>
        */
        $firstPass = $routeInfo[1][DaftRequestInterceptor::class];

        /**
        * @psalm-var array<int, class-string<DaftResponseModifier>>
        *
        * @var array<int, string>
        */
        $secondPass = $routeInfo[1][DaftResponseModifier::class];

        $resp = $this->RunMiddlewareFirstPass($request, ...$firstPass);

        if ( ! ($resp instanceof Response)) {
            /**
            * @var Response
            */
            $resp = $route::DaftRouterHandleRequest(
                $request,
                $routeArgs
            );
        }

        $resp = $this->RunMiddlewareSecondPass($request, $resp, ...$secondPass);

        return $resp;
    }

    /**
    * @psalm-param class-string<DaftRequestInterceptor> ...$middlewares
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
    * @psalm-param class-string<DaftResponseModifier> ...$middlewares
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
