<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftRouter\Router;

use Closure;
use InvalidArgumentException;
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
        * @var string $thing
        */
        foreach ($this->collector->Collect(...$sources) as $thing) {
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
            /**
            * @var string $method
            * @var array<string, array<int, string>> $uris
            */
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
        return array_values(array_filter($this->middleware, function (string $middleware) : bool {
            return
                is_a($middleware, DaftRequestInterceptor::class, true) ||
                is_a($middleware, DaftResponseModifier::class, true);
        }));
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
        * @var string $exception
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

    final protected function MiddlewareNotExcludedFromUri(string $uri) : array
    {
        return array_filter(
            $this->ObtainMiddleware(),

            /**
            * @psalm-suppress InvalidStringClass
            */
            function (string $middleware) use ($uri) : bool {
                $any = $this->MiddlewareNotExcludedFromUriExceptions($middleware, $uri);

                /**
                * @var string $requirement
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
    }

    /**
    * @psalm-suppress InvalidStringClass
    */
    final protected function CompileDispatcherArray() : array
    {
        $out = [];

        foreach ($this->routes as $route) {
            /**
            * @var string $uri
            * @var array<int, string> $methods
            */
            foreach ($route::DaftRouterRoutes() as $uri => $methods) {
                foreach ($methods as $method) {
                    $out[$method][$uri] = $this->MiddlewareNotExcludedFromUri($uri);

                    $out[$method][$uri][] = $route;
                }
            }
        }

        return $out;
    }
}
