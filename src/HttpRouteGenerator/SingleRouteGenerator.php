<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftRouter\HttpRouteGenerator;

use InvalidArgumentException;
use SignpostMarv\DaftRouter\DaftRoute;

abstract class SingleRouteGenerator implements HttpRouteGenerator
{
    /**
    * @var string
    *
    * @psalm-var class-string<DaftRoute>
    */
    protected $route;

    public function __construct(string $route)
    {
        if ( ! is_a($route, DaftRoute::class, true)) {
            throw new InvalidArgumentException(
                'Argument 1 passed to ' .
                __METHOD__ .
                ' must be an implementation of ' .
                DaftRoute::class .
                ', ' .
                $route .
                ' given!'
            );
        }

        $this->route = $route;
    }
}
