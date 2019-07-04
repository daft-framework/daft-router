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
* @template R_TYPED as Response
* @template HTTP_METHOD as 'GET'|'POST'|'CONNECT'|'DELETE'|'HEAD'|'OPTIONS'|'PATCH'|'PURGE'|'PUT'|'TRACE'
* @template HTTP_METHOD_DEFAULT as 'GET'|'POST'|'CONNECT'|'DELETE'|'HEAD'|'OPTIONS'|'PATCH'|'PURGE'|'PUT'|'TRACE'
*
* @template-implements DaftRouteAcceptsTypedArgs<T1, T1_STRINGS, T2, Response, R_TYPED, HTTP_METHOD, HTTP_METHOD_DEFAULT>
*/
abstract class DaftRouteAcceptsOnlyTypedArgs implements DaftRouteAcceptsTypedArgs
{
    /**
    * @template-use DaftRouterAutoMethodCheckingTrait<HTTP_METHOD>
    */
    use DaftRouterAutoMethodCheckingTrait;

    /**
    * @deprecated
    *
    * @psalm-suppress MoreSpecificImplementedParamType
    *
    * @param T2 $args
    *
    * @return R_TYPED
    */
    final public static function DaftRouterHandleRequest(Request $request, $args) : Response
    {
        static::DaftRouterAutoMethodChecking($request->getMethod());

        /**
        * @var R_TYPED
        */
        return static::DaftRouterHandleRequestWithTypedArgs($request, $args);
    }

    /**
    * @param T2 $args
    *
    * @return R_TYPED
    */
    abstract public static function DaftRouterHandleRequestWithTypedArgs(
        Request $request,
        TypedArgs $args
    ) : Response;

    /**
    * @psalm-suppress MoreSpecificImplementedParamType
    *
    * @param T2 $args
    * @param HTTP_METHOD|null $method
    *
    * @throws \InvalidArgumentException if no uri could be found
    */
    final public static function DaftRouterHttpRoute($args, string $method = null) : string
    {
        return static::DaftRouterHttpRouteWithTypedArgs($args, $method);
    }

    /**
    * @param T2 $args
    * @param HTTP_METHOD|null $method
    *
    * @throws \InvalidArgumentException if no uri could be found
    */
    abstract public static function DaftRouterHttpRouteWithTypedArgs(
        TypedArgs $args,
        string $method = null
    ) : string;
}
