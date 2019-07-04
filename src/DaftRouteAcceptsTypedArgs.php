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
* @template T1 as array<string, scalar|DateTimeImmutable|null>
* @template T1_STRINGS as array<string, string|null>
* @template T2 as TypedArgs
* @template R_EMPTY as Response
* @template R_TYPED as Response
* @template HTTP_METHOD as 'GET'|'POST'|'CONNECT'|'DELETE'|'HEAD'|'OPTIONS'|'PATCH'|'PURGE'|'PUT'|'TRACE'
* @template HTTP_METHOD_DEFAULT as 'GET'|'POST'|'CONNECT'|'DELETE'|'HEAD'|'OPTIONS'|'PATCH'|'PURGE'|'PUT'|'TRACE'
*
* @template-extends DaftRoute<T1, T1_STRINGS, T2, R_EMPTY, R_TYPED, HTTP_METHOD, HTTP_METHOD_DEFAULT>
*/
interface DaftRouteAcceptsTypedArgs extends DaftRoute
{
    /**
    * @param T2 $args
    *
    * @return R_EMPTY
    */
    public static function DaftRouterHandleRequestWithTypedArgs(
        Request $request,
        TypedArgs $args
    ) : Response;

    /**
    * @param T1_STRINGS|array<empty, empty> $args
    * @param HTTP_METHOD|null $method
    *
    * @return T2|EmptyArgs
    */
    public static function DaftRouterHttpRouteArgsTyped(array $args, string $method = null);

    /**
    * @param T2 $args
    * @param HTTP_METHOD|null $method
    *
    * @throws \InvalidArgumentException if no uri could be found
    */
    public static function DaftRouterHttpRouteWithTypedArgs(
        TypedArgs $args,
        string $method = null
    ) : string;
}
