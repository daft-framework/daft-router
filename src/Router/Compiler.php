<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftRouter\Router;

use Closure;
use FastRoute\Dispatcher;
use FastRoute\RouteCollector;
use Generator;
use InvalidArgumentException;
use RuntimeException;
use SignpostMarv\DaftRouter\DaftMiddleware;
use SignpostMarv\DaftRouter\DaftRoute;
use SignpostMarv\DaftRouter\DaftSource;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use function FastRoute\cachedDispatcher;

class Compiler
{
    /**
    * @var array<int, string>
    */
    private $routes = [];

    /**
    * @var array<int, string>
    */
    private $middleware = [];

    /**
    * @var array<int, string>
    */
    private $processedSources = [];

    protected function __construct()
    {
    }

    public function AddRoute(string $route) : void
    {
        if ( ! is_a($route, DaftRoute::class, true)) {
            throw new InvalidArgumentException(sprintf(
                'Argument 1 passed to %s must be an implementation of %s',
                __METHOD__,
                DaftRoute::class
            ));
        }

        $this->routes[] = $route;
    }

    public function AddMiddleware(string $middleware) : void
    {
        if ( ! is_a($middleware, DaftMiddleware::class, true)) {
            throw new InvalidArgumentException(sprintf(
                'Argument 1 passed to %s must be an implementation of %s',
                __METHOD__,
                DaftMiddleware::class
            ));
        }

        $this->middleware[] = $middleware;
    }

    public function NudgeCompilerWithSources(string ...$sources) : void
    {
        foreach ($this->RoutesAndMiddleware(...$sources) as $thing) {
            if (is_a($thing, DaftRoute::class, true)) {
                $this->AddRoute((string) $thing);
            }
            if (is_a($thing, DaftMiddleware::class, true)) {
                $this->AddMiddleware((string) $thing);
            }
        }
    }

    final public function CompileDispatcherClosure(string ...$sources) : Closure
    {
        $this->NudgeCompilerWithSources(...$sources);

        $out = [];

        foreach ($this->routes as $route) {
            foreach ($route::DaftRouterRoutes() as $uri => $methods) {
                foreach ($methods as $method) {
                    if ( ! isset($out[$method])) {
                        $out[$method] = [];
                    }

                    $out[$method][$uri] = [];

                    foreach ($this->middleware as $middleware) {
                        $addMiddleware = true;

                        foreach ($middleware::DaftRouterRoutePrefixExceptions() as $exception) {
                            if (0 === mb_strpos($uri, $exception)) {
                                $addMiddleware = false;
                                break;
                            }
                        }

                        if ($addMiddleware) {
                            $out[$method][$uri][] = $middleware;
                        }
                    }

                    $out[$method][$uri][] = $route;
                }
            }
        }

        return function (RouteCollector $collector) use ($out) : void {
            foreach ($out as $method => $uris) {
                foreach ($uris as $uri => $handlers) {
                    $collector->addRoute($method, $uri, $handlers);
                }
            }
        };
    }

    public static function ObtainDispatcher(array $options, string ...$sources) : Dispatcher
    {
        $compiler = new self();

        return cachedDispatcher($compiler->CompileDispatcherClosure(...$sources), $options);
    }

    public static function Handle(
        Dispatcher $dispatcher,
        Request $request,
        string $prefix = ''
    ) : Response {
        $uri = parse_url($request->getUri(), PHP_URL_PATH);

        if ('' !== $prefix) {
            $uri = preg_replace('/^' . preg_quote($prefix, '/') . '/', '', $uri);
        }

        if ('/' !== mb_substr($uri, 0, 1)) {
            $uri = '/' . $uri;
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

        if ( ! is_a($route, DaftRoute::class, true)) {
            throw new RuntimeException(
                'Dispatcher generated a found response without a route handler!'
            );
        }

        $resp = null;

        foreach ($middlewares as $middleware) {
            $resp = $middleware::DaftRouterMiddlewareHandler($request, $resp);
        }

        if ($resp instanceof Response) {
            return $resp;
        }

        return $route::DaftRouterHandleRequest($request, (array) ($routeInfo[2] ?? []));
    }

    final public function ObtainRoutes() : array
    {
        return $this->routes;
    }

    final public function ObtainMiddleware() : array
    {
        return $this->middleware;
    }

    protected function RoutesAndMiddleware(string ...$sources) : Generator
    {
        foreach ($sources as $source) {
            yield $source;
            if (
                is_a($source, DaftSource::class, true) &&
                ! in_array($source, $this->processedSources, true)
            ) {
                $this->processedSources[] = $source;
                yield from $this->RoutesAndMiddleware(
                    ...$source::DaftRouterRouteAndMiddlewareSources()
                );
            }
        }
    }
}
