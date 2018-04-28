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
use SignpostMarv\DaftRouter\DaftMiddleware;
use SignpostMarv\DaftRouter\DaftRoute;
use SignpostMarv\DaftRouter\DaftSource;
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

    final public function CompileDispatcherArray(string ...$sources) : array
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

        return $out;
    }

    final public function CompileDispatcherClosure(string ...$sources) : Closure
    {
        return function (RouteCollector $collector) use ($sources) : void {
            foreach ($this->CompileDispatcherArray(...$sources) as $method => $uris) {
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
