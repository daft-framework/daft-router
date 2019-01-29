<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftRouter\Router;

use FastRoute\RouteCollector as Base;
use InvalidArgumentException;
use SignpostMarv\DaftRouter\DaftRoute;
use SignpostMarv\DaftRouter\DaftRouteFilter;

final class RouteCollector extends Base
{
    /**
    * @param string|string[] $httpMethod
    * @param string $route
    * @param mixed $handler
    */
    public function addRoute($httpMethod, $route, $handler) : void
    {
        if ( ! is_array($handler)) {
            throw new InvalidArgumentException(sprintf(
                'Argument %u passed to %s must be an array!',
                3,
                __METHOD__
            ));
        }

        if (is_array($httpMethod)) {
            foreach ($httpMethod as $method) {
                $this->addRouteStrict($method, $route, $handler);
            }

            return;
        }

        $this->addRouteStrict($httpMethod, $route, $handler);
    }

    private function addRouteStrict(string $httpMethod, string $route, array $handler) : void
    {
        /**
        * @var string
        */
        $routeClass = array_pop($handler);

        $handler = array_map(
            function (array $handler) : array {
                return array_values(
                    array_filter($handler, function (string $maybeMiddleware) : bool {
                        return is_a($maybeMiddleware, DaftRouteFilter::class, true);
                    }
                ));
            },
            array_filter($handler, 'is_array')
        );

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
