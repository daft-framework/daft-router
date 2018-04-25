<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftRouter;

interface DaftSource
{
    /**
    * Provides an array of DaftRoute, DaftMiddleware, or DaftSource implementations.
    *
    * @return array<int, string>
    */
    public static function DaftRouterRouteAndMiddlewareSources() : array;
}
