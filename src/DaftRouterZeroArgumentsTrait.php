<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftRouter;

use InvalidArgumentException;

/**
* @template TYPED as array<empty, empty>
*/
trait DaftRouterZeroArgumentsTrait
{
    /**
    * @param array<string, scalar> $args
    *
    * @psalm-param TYPED $args
    *
    * @return array<string, scalar>
    *
    * @psalm-return TYPED
    */
    public static function DaftRouterHttpRouteArgsTyped(array $args, string $method) : array
    {
        if (count($args) > 0) {
            throw new InvalidArgumentException('This route takes no arguments!');
        }

        return [];
    }
}
