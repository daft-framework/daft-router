<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftRouter\Router;

use Closure;
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

    final public function CompileDispatcherClosure(string ...$sources) : Closure
    {
        $this->NudgeCompilerWithSources(...$sources);

        return function (RouteCollector $collector) : void {
            foreach ($this->CompileDispatcherArray() as $method => $uris) {
                foreach ($uris as $uri => $handlers) {
                    $collector->addRoute($method, $uri, $handlers);
                }
            }
        };
    }

    public static function ObtainDispatcher(array $options, string ...$sources) : Dispatcher
    {
        $compiler = new self();
        $options['dispatcher'] = Dispatcher::class;
        $options['routeCollector'] = RouteCollector::class;

        /**
        * @var Dispatcher $out
        */
        $out = cachedDispatcher($compiler->CompileDispatcherClosure(...$sources), $options);

        return $out;
    }

    final public function ObtainRoutes() : array
    {
        return $this->routes;
    }

    final public function ObtainMiddleware() : array
    {
        return $this->middleware;
    }

    final protected function MiddlewareNotExcludedFromUri(string $uri) : array
    {
        return array_filter($this->middleware, function (string $middleware) use ($uri) : bool {
            foreach ($middleware::DaftRouterRoutePrefixExceptions() as $exception) {
                if (0 === mb_strpos($uri, $exception)) {
                    return false;
                }
            }

            return true;
        });
    }

    final protected function CompileDispatcherArray() : array
    {
        $out = [];

        foreach ($this->routes as $route) {
            foreach ($route::DaftRouterRoutes() as $uri => $methods) {
                foreach ($methods as $method) {
                    $out[$method][$uri] = $this->MiddlewareNotExcludedFromUri($uri);

                    $out[$method][$uri][] = $route;
                }
            }
        }

        return $out;
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
