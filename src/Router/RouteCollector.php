<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftRouter\Router;

use FastRoute\RouteCollector as Base;
use InvalidArgumentException;
use SignpostMarv\DaftRouter\DaftRoute;
use SignpostMarv\DaftRouter\DaftRequestInterceptor;
use SignpostMarv\DaftRouter\DaftResponseModifier;

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
        if (is_array($httpMethod)) {
            foreach ($httpMethod as $method) {
                $this->addRouteStrict($method, $route, $handler);
            }

            return;
        }

        $this->addRouteStrict($httpMethod, $route, $handler);
    }

    /**
    * @psalm-param array{DaftRequestInterceptor::class:array<int, class-string<DaftRequestInterceptor>>, DaftResponseModifier::class:array<int, class-string<DaftResponseModifier>>, 0:class-string<DaftRoute>} $handler
    */
    private function addRouteStrict(string $httpMethod, string $route, array $handler) : void
    {
        parent::addRoute($httpMethod, $route, $handler);
    }
}
