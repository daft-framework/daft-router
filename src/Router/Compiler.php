<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftRouter\Router;

use Closure;
use InvalidArgumentException;
use RuntimeException;
use SignpostMarv\DaftInterfaceCollector\StaticMethodCollector;
use SignpostMarv\DaftRouter\DaftRequestInterceptor;
use SignpostMarv\DaftRouter\DaftResponseModifier;
use SignpostMarv\DaftRouter\DaftRoute;
use SignpostMarv\DaftRouter\DaftRouteFilter;
use SignpostMarv\DaftRouter\DaftSource;
use function FastRoute\cachedDispatcher;

class Compiler
{
    const CollectorConfig = [
        DaftSource::class => [
            'DaftRouterRouteAndMiddlewareSources' => [
                DaftRequestInterceptor::class,
                DaftResponseModifier::class,
                DaftRoute::class,
                DaftSource::class,
            ],
        ],
    ];

    const CollectorInterfacesConfig = [
        DaftRequestInterceptor::class,
        DaftResponseModifier::class,
        DaftRoute::class,
    ];

    /**
    * @var array<int, string>
    */
    private $routes = [];

    /**
    * @var array<int, string>
    */
    private $middleware = [DaftRouteFilter::class];

    /**
    * @var StaticMethodCollector
    */
    private $collector;

    protected function __construct()
    {
        $this->collector = new StaticMethodCollector(
            self::CollectorConfig,
            self::CollectorInterfacesConfig
        );
    }

    public function AddRoute(string $route) : void
    {
        if ( ! is_a($route, DaftRoute::class, true)) {
            throw new InvalidArgumentException(sprintf(
                'Argument 1 passed to %s must be an implementation of %s',
                __METHOD__,
                DaftRoute::class
            ));
        } elseif ( ! in_array($route, $this->routes, true)) {
            $this->routes[] = $route;
        }
    }

    public function AddMiddleware(string $middleware) : void
    {
        if ( ! is_a($middleware, DaftRouteFilter::class, true)) {
            throw new InvalidArgumentException(sprintf(
                'Argument 1 passed to %s must be an implementation of %s',
                __METHOD__,
                DaftRouteFilter::class
            ));
        } elseif ( ! in_array($middleware, $this->middleware, true)) {
            $this->middleware[] = $middleware;
        }
    }

    public function NudgeCompilerWithSources(string ...$sources) : void
    {
        /**
        * @var iterable<scalar|array|object|null>
        */
        $things = $this->collector->Collect(...$sources);
        foreach ($things as $thing) {
            if ( ! is_string($thing)) {
                continue;
            }
            if (is_a($thing, DaftRoute::class, true)) {
                $this->AddRoute($thing);
            }
            if (is_a($thing, DaftRouteFilter::class, true)) {
                $this->AddMiddleware($thing);
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

        return static::EnsureDispatcherIsCorrectlyTyped(
            cachedDispatcher($compiler->CompileDispatcherClosure(...$sources), $options)
        );
    }

    final public function ObtainRoutes() : array
    {
        return $this->routes;
    }

    final public function ObtainMiddleware() : array
    {
        return array_values(array_filter($this->middleware, function (string $middleware) : bool {
            return
                is_a($middleware, DaftRequestInterceptor::class, true) ||
                is_a($middleware, DaftResponseModifier::class, true);
        }));
    }

    /**
    * @param mixed $out
    */
    final protected static function EnsureDispatcherIsCorrectlyTyped($out) : Dispatcher
    {
        if ( ! ($out instanceof Dispatcher)) {
            throw new RuntimeException(sprintf(
                'cachedDispatcher expected to return instance of %s, returned instead "%s"',
                Dispatcher::class,
                (is_object($out) ? get_class($out) : gettype($out))
            ));
        }

        return $out;
    }

    /**
    * @psalm-suppress InvalidStringClass
    */
    final protected function MiddlewareNotExcludedFromUriExceptions(
        string $middleware,
        string $uri
    ) : bool {
        $any = false;
        /**
        * @var string
        */
        foreach ($middleware::DaftRouterRoutePrefixExceptions() as $exception) {
            if (0 === mb_strpos($uri, $exception)) {
                if ( ! $any) {
                    return false;
                }
            } else {
                $any = true;
            }
        }

        return $any;
    }

    /**
    * @return array<int, string>
    */
    final protected function MiddlewareNotExcludedFromUri(string $uri) : array
    {
        /**
        * @var array<int, string>
        */
        $out = array_filter(
            $this->ObtainMiddleware(),

            /**
            * @psalm-suppress InvalidStringClass
            */
            function (string $middleware) use ($uri) : bool {
                $any = $this->MiddlewareNotExcludedFromUriExceptions($middleware, $uri);

                /**
                * @var string
                */
                foreach ($middleware::DaftRouterRoutePrefixRequirements() as $requirement) {
                    $pos = mb_strpos($uri, $requirement);
                    if (false === $pos || $pos > 0) {
                        return false;
                    }
                }

                return $any;
            }
        );

        return $out;
    }

    /**
    * @psalm-suppress InvalidStringClass
    *
    * @return array<string, array<string, array<int, string>>>
    */
    final protected function CompileDispatcherArray() : array
    {
        /**
        * @var array<string, array<string, array<int, string>>>
        */
        $out = [];

        foreach ($this->routes as $route) {
            /**
            * @var array<string, array<int, string>>
            */
            $routes = $route::DaftRouterRoutes();

            foreach ($routes as $uri => $methods) {
                foreach ($methods as $method) {
                    $out[$method][$uri] = $this->MiddlewareNotExcludedFromUri($uri);

                    $out[$method][$uri][] = $route;
                }
            }
        }

        return $out;
    }
}
