<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftRouter\Router;

use Closure;
use FastRoute\Dispatcher;
use FastRoute\RouteCollector;
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

    final public function CompileDispatcherClosure(string ...$sources) : Closure
    {
        $this->CompileFromSources(...$sources);

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
        $uri = $request->getUri();

        if ('' !== $prefix) {
            $uri = preg_replace('/^' . preg_quote($prefix, '/') . '/', '', $uri);
        }

        if ('/' !== mb_substr($uri, 0, 1)) {
            $uri = '/' . $uri;
        }

        $routeInfo = $dispatcher->dispatch($request->getMethod(), $uri);

        if (
            ! is_array($routeInfo) ||
            (
                ! isset($routeInfo[0]) ||
                (
                    Dispatcher::FOUND === $routeInfo[0] &&
                    (
                        ! isset($routeInfo[1], $routeInfo[2]) ||
                        ! is_array($routeInfo[1]) ||
                        ! is_array($routeInfo[2]) ||
                        count($routeInfo[1]) < 1
                    )
                ) ||
                (
                    Dispatcher::NOT_FOUND !== $routeInfo[0] &&
                    Dispatcher::METHOD_NOT_ALLOWED !== $routeInfo[0] &&
                    Dispatcher::FOUND !== $routeInfo[0]
                )
            )
        ) {
            return new Response('Unknown error', Response::HTTP_INTERNAL_SERVER_ERROR, [
                'content-type' => 'text/plain',
            ]);
        }

        if (Dispatcher::NOT_FOUND === $routeInfo[0]) {
            return new Response('404 Not Found', Response::HTTP_NOT_FOUND, [
                'content-type' => 'text/plain',
            ]);
        } elseif (Dispatcher::METHOD_NOT_ALLOWED === $routeInfo[0]) {
            return new Response('Method Not Allowed', Response::HTTP_METHOD_NOT_ALLOWED, [
                'content-type' => 'text/plain',
            ]);
        } elseif ( ! isset($routeInfo[1])) {
            throw new RuntimeException(
                'Dispatcher generated a found response with no handler data!'
            );
        } elseif ( ! is_array($routeInfo[1])) {
            throw new RuntimeException(
                'Dispatcher generated a found response with invalid handler data!'
            );
        } elseif (count($routeInfo[1]) < 1) {
            throw new RuntimeException(
                'Dispatcher generated a found response with empty handler data!'
            );
        } elseif ( ! is_array($routeInfo[2])) {
            throw new RuntimeException(
                'Dispatcher generated a found response with invalid variable data!'
            );
        }

        $middlewares = array_values($routeInfo[1]);
        $route = array_pop($middlewares);

        if ( ! is_a($route, DaftRoute::class, true)) {
            throw new RuntimeException(
                'Dispatcher generated a found response without a route handler!'
            );
        }

        $resp = null;

        foreach ($middlewares as $middleware) {
            if ( ! is_a($middleware, DaftMiddleware::class, true)) {
                throw new RuntimeException(
                    'Dispatcher generated a found response with invalid middlware data!'
                );
            }

            $resp = $middleware::DaftRouterMiddlewareHandler($request, $resp);
        }

        if ($resp instanceof Response) {
            return $resp;
        }

        return $route::DaftRouterHandleRequest($request, $routeInfo[2]);
    }

    final public function ObtainRoutes() : array
    {
        return $this->routes;
    }

    final public function ObtainMiddleware() : array
    {
        return $this->middleware;
    }

    protected function CompileFromSources(string ...$sources) : void
    {
        foreach ($sources as $source) {
            if (is_a($source, DaftRoute::class, true)) {
                $routes = $source::DaftRouterRoutes();
                foreach (array_keys($routes) as $uri) {
                    if ('/' !== mb_substr($uri, 0, 1)) {
                        throw new InvalidArgumentException(
                            'All route uris must begin with a forward slash!'
                        );
                    } elseif ( ! is_array($routes[$uri])) {
                        throw new InvalidArgumentException(
                            'All route uris must be specified with an array of HTTP methods!'
                        );
                    }

                    foreach ($routes[$uri] as $k => $v) {
                        if ( ! is_int($k)) {
                            throw new InvalidArgumentException(
                                'All http methods must be specified with numeric indices!'
                            );
                        } elseif ( ! is_string($v)) {
                            throw new InvalidArgumentException(
                                'All http methods must be specified as an array of strings!'
                            );
                        }
                    }
                }
                $this->AddRoute($source);
            }
            if (is_a($source, DaftMiddleware::class, true)) {
                foreach (array_values($source::DaftRouterRoutePrefixExceptions()) as $uriPrefix) {
                    if ('/' !== mb_substr($uriPrefix, 0, 1)) {
                        throw new InvalidArgumentException(
                            'All middleware uri prefixes must begin with a forward slash!'
                        );
                    }
                }
                $this->AddMiddleware($source);
            }
            if (
                is_a($source, DaftSource::class, true) &&
                ! in_array($source, $this->processedSources, true)
            ) {
                $this->processedSources[] = $source;
                $this->CompileFromSources(...$source::DaftRouterRouteAndMiddlewareSources());
            }
        }
    }
}
