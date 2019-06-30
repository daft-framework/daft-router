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
* @template T3 as Response
* @template T4 as Response
*/
interface DaftRoute
{
    /**
    * @deprecated
    *
    * @param T2|EmptyArgs $args
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
    * @param T2|EmptyArgs $args
    *
    * @throws \InvalidArgumentException if no uri could be found
    */
    public static function DaftRouterHttpRoute($args, string $method = 'GET') : string;

    /**
    * @param T1_STRINGS|array<empty, empty> $args
    *
    * @return T2|EmptyArgs
    */
    public static function DaftRouterHttpRouteArgsTyped(array $args, string $method);
}
