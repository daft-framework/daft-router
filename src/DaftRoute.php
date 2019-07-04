<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftRouter;

use DateTimeImmutable;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
* This will be flagged as deprecated soon.
*
* @template T1 as array<string, scalar|DateTimeImmutable|null>
* @template T1_STRINGS as array<string, string|null>
* @template T2 as TypedArgs
* @template R_EMPTY as Response
* @template R_TYPED as Response
* @template HTTP_METHOD as 'GET'|'POST'|'CONNECT'|'DELETE'|'HEAD'|'OPTIONS'|'PATCH'|'PURGE'|'PUT'|'TRACE'
* @template HTTP_METHOD_DEFAULT as 'GET'|'POST'|'CONNECT'|'DELETE'|'HEAD'|'OPTIONS'|'PATCH'|'PURGE'|'PUT'|'TRACE'
*/
interface DaftRoute
{
    /**
    * @deprecated
    *
    * @param T2|EmptyArgs $args
    *
    * @return R_EMPTY|R_TYPED
    */
    public static function DaftRouterHandleRequest(Request $request, $args) : Response;

    /**
    * @return array<string, array<int, HTTP_METHOD>> an array of URIs & methods
    */
    public static function DaftRouterRoutes() : array;

    /**
    * @deprecated
    *
    * @param T2|EmptyArgs $args
    * @param HTTP_METHOD|null $method If null, use DaftRoute::DaftRouterHttpRouteDefaultMethod()
    *
    * @throws \InvalidArgumentException if no uri could be found
    */
    public static function DaftRouterHttpRoute($args, string $method = null) : string;

    /**
    * @return HTTP_METHOD_DEFAULT
    */
    public static function DaftRouterHttpRouteDefaultMethod() : string;

    /**
    * @param T1_STRINGS|array<empty, empty> $args
    * @param HTTP_METHOD|null $method If null, use DaftRoute::DaftRouterHttpRouteDefaultMethod()
    *
    * @return T2|EmptyArgs
    */
    public static function DaftRouterHttpRouteArgsTyped(array $args, string $method = null);
}
