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

    public static function DaftRouterHttpRouteArgsTyped(array $args, string $method) : array
    {
        return static::DaftRouterHttpRouteArgs($args, $method);
    }

    public static function DaftRouterHttpRouteArgs(array $args, string $method) : array
    {
        $method = static::DaftRouterAutoMethodChecking($method);

        if (count($args) > 0) {
            throw new InvalidArgumentException('This route takes no arguments!');
        }

        return [];
    }
}
