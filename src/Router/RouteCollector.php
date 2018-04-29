<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftRouter\Router;

use FastRoute\RouteCollector as Base;
use InvalidArgumentException;
use SignpostMarv\DaftRouter\DaftMiddleware;
use SignpostMarv\DaftRouter\DaftRoute;

final class RouteCollector extends Base
{
    public function addRoute($httpMethod, $route, $handler)
    {
        $this->addRouteStrict($httpMethod, $route, $handler);
    }

    protected function addRouteStrict(string $httpMethod, string $route, array $handler) : void
    {
        $routeClass = array_pop($handler);

        /**
        * @var array<int, DaftMiddleware|DaftRoute|string>
        */
        $handler = array_values(array_filter($handler, function (string $maybeMiddleware) : bool {
            return is_a($maybeMiddleware, DaftMiddleware::class, true);
        }));

        if ( ! is_a($routeClass, DaftRoute::class, true)) {
            throw new InvalidArgumentException(sprintf(
                'Cannot call %s without a trailing implementation of %s',
                __METHOD__,
                DaftRoute::class
            ));
        }

        $handler[] = $routeClass;

        parent::addRoute($httpMethod, $route, $handler);
    }
}
