<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftRouter\Router;

use FastRoute\Dispatcher\GroupCountBased as Base;
use SignpostMarv\DaftRouter\DaftRoute;
use SignpostMarv\DaftRouter\ResponseException;

class Dispatcher extends Base
{
    final public function dispatch($httpMethod, $uri)
    {
        $routeInfo = parent::dispatch($httpMethod, $uri);

        if (Dispatcher::NOT_FOUND === $routeInfo[0]) {
            throw new ResponseException('Dispatcher was not able to generate a response!', 404);
        } elseif (Dispatcher::METHOD_NOT_ALLOWED === $routeInfo[0]) {
            throw new ResponseException('Dispatcher was not able to generate a response!', 405);
        } elseif (
            Base::FOUND === $routeInfo[0] &&
            ! is_a($routeInfo[1][count($routeInfo[1]) - 1], DaftRoute::class, true)
        ) {
            throw new ResponseException(
                'Dispatcher generated a found response without a route handler!',
                500
            );
        }

        return $routeInfo;
    }
}
