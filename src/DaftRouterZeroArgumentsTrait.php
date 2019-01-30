<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftRouter;

use InvalidArgumentException;

trait DaftRouterZeroArgumentsTrait
{
    use DaftRouterAutoMethodCheckingTrait;

    /**
    * @param array<string, string> $args
    *
    * @throws InvalidArgumentException if $args or $method are not supported or invalid
    *
    * @return array<string, string>
    */
    public static function DaftRouterHttpRouteArgsTyped(array $args, string $method) : array
    {
        return static::DaftRouterHttpRouteArgs($args, $method);
    }

    /**
    * @param array<string, string> $args
    *
    * @throws InvalidArgumentException if $args or $method are not supported or invalid
    *
    * @return array<string, mixed>
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
