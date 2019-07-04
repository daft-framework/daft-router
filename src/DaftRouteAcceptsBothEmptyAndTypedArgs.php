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
* @template HTTP_METHOD_EMPTY as 'GET'|'POST'|'CONNECT'|'DELETE'|'HEAD'|'OPTIONS'|'PATCH'|'PURGE'|'PUT'|'TRACE'
* @template HTTP_METHOD_TYPED as 'GET'|'POST'|'CONNECT'|'DELETE'|'HEAD'|'OPTIONS'|'PATCH'|'PURGE'|'PUT'|'TRACE'
* @template HTTP_METHOD_DEFAULT as 'GET'|'POST'|'CONNECT'|'DELETE'|'HEAD'|'OPTIONS'|'PATCH'|'PURGE'|'PUT'|'TRACE'
*
* @template-implements DaftRouteAcceptsEmptyArgs<T1, T1_STRINGS, T2, R_EMPTY, R_TYPED, HTTP_METHOD_EMPTY, HTTP_METHOD_DEFAULT>
* @template-implements DaftRouteAcceptsTypedArgs<T1, T1_STRINGS, T2, R_EMPTY, R_TYPED, HTTP_METHOD_TYPED, HTTP_METHOD_DEFAULT>
*/
abstract class DaftRouteAcceptsBothEmptyAndTypedArgs implements DaftRouteAcceptsEmptyArgs, DaftRouteAcceptsTypedArgs
{
    /**
    * @template-use DaftRouterAutoMethodCheckingTrait<HTTP_METHOD_EMPTY|HTTP_METHOD_TYPED>
    */
    use DaftRouterAutoMethodCheckingTrait;

    /**
    * @param T2|EmptyArgs $args
    *
    * @return R_EMPTY|R_TYPED
    */
    final public static function DaftRouterHandleRequest(
        Request $request,
        $args
    ) : Response {
        static::DaftRouterAutoMethodChecking($request->getMethod());

        if ($args instanceof TypedArgs) {
            /**
            * @var R_TYPED
            */
            return static::DaftRouterHandleRequestWithTypedArgs($request, $args);
        }

        /**
        * @var R_EMPTY
        */
        return static::DaftRouterHandleRequestWithEmptyArgs($request);
    }

    /**
    * @param T1_STRINGS|array<empty, empty> $args
    * @param HTTP_METHOD_TYPED|null $method
    *
    * @return T2|EmptyArgs
    */
    abstract public static function DaftRouterHttpRouteArgsTyped(
        array $args,
        string $method = null
    );

    /**
    * @param T2 $args
    *
    * @return R_EMPTY
    */
    abstract public static function DaftRouterHandleRequestWithEmptyArgs(
        Request $request
    ) : Response;

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
    * @param T2|EmptyArgs $args
    * @param HTTP_METHOD_EMPTY|HTTP_METHOD_TYPED|null $method
    */
    final public static function DaftRouterHttpRoute(
        $args,
        string $method = null
    ) : string {
        if ($args instanceof TypedArgs) {
            /**
            * @var HTTP_METHOD_TYPED|null
            */
            $method = $method;

            return static::DaftRouterHttpRouteWithTypedArgs($args, $method);
        }

        /**
        * @var HTTP_METHOD_EMPTY|null
        */
        $method = $method;

        return static::DaftRouterHttpRouteWithEmptyArgs($method);
    }

    /**
    * @param T2 $args
    * @param HTTP_METHOD_TYPED|null $method
    *
    * @return string
    */
    abstract public static function DaftRouterHttpRouteWithTypedArgs(
        TypedArgs $args,
        string $method = null
    ) : string;
}
