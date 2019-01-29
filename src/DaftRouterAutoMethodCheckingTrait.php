<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftRouter;

use InvalidArgumentException;

trait DaftRouterAutoMethodCheckingTrait
{
    /**
    * @return array<string, array<int, string>> an array of URIs & methods
    */
    abstract public static function DaftRouterRoutes() : array;

    protected static function DaftRouterAutoMethodChecking(string $method) : string
    {
        $methods = [];

        /**
        * @var array<string, array<int, string>>
        */
        $routes = static::DaftRouterRoutes();

        foreach ($routes as $uri => $routeMethods) {
            $methods = array_merge($routeMethods, $routeMethods);
        }

        if ( ! in_array($method, $methods, true)) {
            throw new InvalidArgumentException('Specified method not supported!');
        }

        return $method;
    }
}
