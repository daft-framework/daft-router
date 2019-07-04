<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftRouter\Router;

use FastRoute\Dispatcher\GroupCountBased as Base;
use RuntimeException;
use SignpostMarv\DaftRouter\DaftRequestInterceptor;
use SignpostMarv\DaftRouter\DaftResponseModifier;
use SignpostMarv\DaftRouter\DaftRouteAcceptsEmptyArgs;
use SignpostMarv\DaftRouter\DaftRouteAcceptsTypedArgs;
use SignpostMarv\DaftRouter\EmptyArgs;
use SignpostMarv\DaftRouter\ResponseException;
use SignpostMarv\DaftRouter\TypedArgs;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
* @template HTTP_METHOD as 'GET'|'POST'|'CONNECT'|'DELETE'|'HEAD'|'OPTIONS'|'PATCH'|'PURGE'|'PUT'|'TRACE'
*/
class Dispatcher extends Base
{
    const INT_ARRAY_INDEX_ROUTE_ARGS = 2;

    /**
    * @param HTTP_METHOD $httpMethod
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
        $routeInfo = $this->handleDispatch($request, $prefix);

        return $this->handleRouteInfo($request, $routeInfo);
    }

    /**
    * @return array{1:array, 2:array<string, string>}
    */
    protected function handleDispatch(Request $request, string $prefix = '') : array
    {
        $regex = '/^' . preg_quote($prefix, '/') . '/';
        /**
        * @var string
        */
        $path = parse_url($request->getUri(), PHP_URL_PATH);
        $path = preg_replace($regex, '', $path);
        $path = implode(
            '/',
            array_map('rawurldecode', explode('/', str_replace('//', '/', ('/' . $path))))
        );

        /**
        * @var HTTP_METHOD
        */
        $method = $request->getMethod();

        /**
        * @var array{1:array, 2:array<string, string>}
        */
        $routeInfo = $this->dispatch($method, $path);

        return $routeInfo;
    }

    /**
    * @param array{1:array, 2:array<string, string>} $routeInfo
    */
    protected function handleRouteInfo(Request $request, array $routeInfo) : Response
    {
        $routeArgs = new EmptyArgs();

        /**
        * @var class-string<DaftRouteAcceptsEmptyArgs>|class-string<DaftRouteAcceptsTypedArgs>
        */
        $route = array_pop($routeInfo[1]) ?: '';

        if (isset($routeInfo[self::INT_ARRAY_INDEX_ROUTE_ARGS])) {
            /**
            * @var HTTP_METHOD
            */
            $method = $request->getMethod();

            $routeArgs = $route::DaftRouterHttpRouteArgsTyped(
                $routeInfo[self::INT_ARRAY_INDEX_ROUTE_ARGS],
                $method
            );
        }

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

        return $this->handleRouteInfoResponse(
            $request,
            $route,
            $routeArgs,
            $firstPass,
            $secondPass
        );
    }

    /**
    * @param class-string<DaftRouteAcceptsEmptyArgs>|class-string<DaftRouteAcceptsTypedArgs> $route
    * @param EmptyArgs|TypedArgs $routeArgs
    * @param array<int, class-string<DaftRequestInterceptor>> $firstPass
    * @param array<int, class-string<DaftResponseModifier>> $secondPass
    */
    protected function handleRouteInfoResponse(
        Request $request,
        string $route,
        $routeArgs,
        array $firstPass,
        array $secondPass
    ) : Response {
        $resp = $this->RunMiddlewareFirstPass($request, ...$firstPass);

        if ( ! ($resp instanceof Response)) {
            if (
                0 === count($routeArgs) &&
                is_a($route, DaftRouteAcceptsEmptyArgs::class, true)
            ) {
                $resp = $route::DaftRouterHandleRequestWithEmptyArgs($request);
            } elseif (
                ($routeArgs instanceof TypedArgs) &&
                is_a($route, DaftRouteAcceptsTypedArgs::class, true)
            ) {
                $resp = $route::DaftRouterHandleRequestWithTypedArgs($request, $routeArgs);
            } else {
                throw new RuntimeException(
                    'Untyped request handling is deprecated!'
                );
            }
        }

        $resp = $this->RunMiddlewareSecondPass($request, $resp, ...$secondPass);

        return $resp;
    }

    /**
    * @psalm-param class-string<DaftRequestInterceptor> ...$middlewares
    */
    protected function RunMiddlewareFirstPass(Request $request, string ...$middlewares) : ? Response
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
    protected function RunMiddlewareSecondPass(
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
