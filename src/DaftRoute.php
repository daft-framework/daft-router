<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftRouter;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
* This will be flagged as deprecated soon.
*
* @template T1 as array<string, scalar>|array<empty, empty>
* @template T2 as TypedArgs|EmptyArgs
* @template T3 as Response
* @template T4 as Response
*/
interface DaftRoute
{
    /**
    * @deprecated
    *
    * @param T2 $args
    *
    * @return T3|T4
    */
    public static function DaftRouterHandleRequest(Request $request, $args) : Response;

    /**
    * @return array<string, array<int, string>> an array of URIs & methods
    */
    public static function DaftRouterRoutes() : array;

    /**
    * @deprecated
    *
    * @param T2 $args
    *
    * @throws \InvalidArgumentException if no uri could be found
    */
    public static function DaftRouterHttpRoute($args, string $method = 'GET') : string;

    /**
    * @template K as key-of<T1>
    *
    * @param array<K, string> $args
    *
    * @return T2
    */
    public static function DaftRouterHttpRouteArgsTyped(array $args, string $method);
}
