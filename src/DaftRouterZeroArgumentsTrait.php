<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftRouter;

use InvalidArgumentException;

/**
* @template ARGS as array<empty, empty>
* @template TYPED as array<empty, empty>
*/
trait DaftRouterZeroArgumentsTrait
{
    use DaftRouterAutoMethodCheckingTrait;

    /**
    * @param array<string, string> $args
    *
    * @psalm-param ARGS $args
    *
    * @return array<string, string>
    *
    * @psalm-return TYPED
    */
    public static function DaftRouterHttpRouteArgsTyped(array $args, string $method) : array
    {
        return static::DaftRouterHttpRouteArgs($args, $method);
    }

    /**
    * @param array<string, string> $args
    *
    * @psalm-param ARGS $args
    *
    * @return array<string, string>
    *
    * @psalm-return TYPED
    */
    public static function DaftRouterHttpRouteArgs(array $args, string $method) : array
    {
        static::DaftRouterAutoMethodChecking($method);

        if (count($args) > 0) {
            throw new InvalidArgumentException('This route takes no arguments!');
        }

        return [];
    }
}
