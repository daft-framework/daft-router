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
*
* @template-implements DaftRouteAcceptsEmptyArgs<T1, T1_STRINGS, T2, R_EMPTY, R_TYPED>
* @template-implements DaftRouteAcceptsTypedArgs<T1, T1_STRINGS, T2, R_EMPTY, R_TYPED>
*/
abstract class DaftRouteAcceptsBothEmptyAndTypedArgs implements DaftRouteAcceptsEmptyArgs, DaftRouteAcceptsTypedArgs
{
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
    *
    * @return T2|EmptyArgs
    */
    abstract public static function DaftRouterHttpRouteArgsTyped(array $args, string $method);

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
    */
    final public static function DaftRouterHttpRoute(
        $args,
        string $method = 'GET'
    ) : string {
        static::DaftRouterAutoMethodChecking($method);

        if ($args instanceof TypedArgs) {
            return static::DaftRouterHttpRouteWithTypedArgs($args, $method);
        }

        return static::DaftRouterHttpRouteWithEmptyArgs($method);
    }

    /**
    * @param T2 $args
    *
    * @return string
    */
    abstract public static function DaftRouterHttpRouteWithTypedArgs(
        TypedArgs $args,
        string $method = 'GET'
    ) : string;
}
