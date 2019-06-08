<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftRouter;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
* @template T1 as array<string, scalar>
* @template T2 as TypedArgs
*/
interface DaftRoute
{
    /**
    * @param T2 $args
    */
    public static function DaftRouterHandleRequest(Request $request, TypedArgs $args) : Response;

    /**
    * @return array<string, array<int, string>> an array of URIs & methods
    */
    public static function DaftRouterRoutes() : array;

    /**
    * @param T2 $args
    *
    * @throws \InvalidArgumentException if no uri could be found
    */
    public static function DaftRouterHttpRoute(TypedArgs $args, string $method = 'GET') : string;

    /**
    * @template K as key-of<T1>
    *
    * @param array<K, string> $args
    *
    * @return T2
    */
    public static function DaftRouterHttpRouteArgsTyped(array $args, string $method) : TypedArgs;
}
