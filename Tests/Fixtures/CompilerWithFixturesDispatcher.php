<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftRouter\Tests\Fixtures;

use function FastRoute\cachedDispatcher;
use SignpostMarv\DaftInterfaceCollector\StaticMethodCollector as BaseStaticMethodCollector;
use SignpostMarv\DaftRouter\DaftRoute;
use SignpostMarv\DaftRouter\DaftRouteFilter;
use SignpostMarv\DaftRouter\DaftSource;
use SignpostMarv\DaftRouter\Router\Compiler as Base;
use SignpostMarv\DaftRouter\Router\Dispatcher as BaseDispatcher;
use SignpostMarv\DaftRouter\Router\RouteCollector;

class CompilerWithFixturesDispatcher extends Compiler
{
    /**
    * @param class-string<DaftRoute>|class-string<DaftRouteFilter>|class-string<DaftSource> ...$sources
    */
    public static function ObtainDispatcher(array $options, string ...$sources) : BaseDispatcher
    {
        $compiler = new self();
        $options['dispatcher'] = Dispatcher::class;
        $options['routeCollector'] = RouteCollector::class;

        return static::EnsureDispatcherIsCorrectlyTyped(cachedDispatcher(
            $compiler->CompileDispatcherClosure(...$sources),
            $options
        ));
    }
}
