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
    * @var class-string<DaftRoute>
    */
    protected $route;

    /**
    * @param class-string<DaftRoute> $route
    */
    public function __construct(string $route)
    {
        $this->route = $route;
    }
}
