<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftRouter\Router;

use FastRoute\RouteCollector as Base;
use SignpostMarv\DaftRouter\DaftRequestInterceptor;
use SignpostMarv\DaftRouter\DaftResponseModifier;
use SignpostMarv\DaftRouter\DaftRoute;

final class RouteCollector extends Base
{
    /**
    * @param string|string[] $httpMethod
    * @param string $route
    * @param mixed $handler
    *
    * @psalm-param array{DaftRequestInterceptor::class:array<int, class-string<DaftRequestInterceptor>>, DaftResponseModifier::class:array<int, class-string<DaftResponseModifier>>, 0:class-string<DaftRoute>} $handler
    */
    public function addRoute($httpMethod, $route, $handler) : void
    {
        foreach ((array) $httpMethod as $method) {
            $this->addRouteStrict($method, $route, $handler);
        }
    }

    /**
    * @psalm-param array{DaftRequestInterceptor::class:array<int, class-string<DaftRequestInterceptor>>, DaftResponseModifier::class:array<int, class-string<DaftResponseModifier>>, 0:class-string<DaftRoute>} $handler
    */
    private function addRouteStrict(string $method, string $route, array $handler) : void
    {
            parent::addRoute($method, $route, $handler);
    }
}
