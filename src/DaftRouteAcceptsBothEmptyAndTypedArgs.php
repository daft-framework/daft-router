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
* @template T3 as Response
* @template T4 as Response
*
* @template-implements DaftRouteAcceptsEmptyArgs<T1, T2, T3>
* @template-implements DaftRouteAcceptsTypedArgs<T1, T2, T4>
*/
abstract class DaftRouteAcceptsBothEmptyAndTypedArgs implements DaftRouteAcceptsEmptyArgs, DaftRouteAcceptsTypedArgs
{
    use DaftRouterAutoMethodCheckingTrait;

    /**
    * @param T2|EmptyArgs $args
    *
    * @return T3|T4
    */
    final public static function DaftRouterHandleRequest(
        Request $request,
        $args
    ) : Response {
        static::DaftRouterAutoMethodChecking($request->getMethod());

        if ($args instanceof TypedArgs) {
            /**
            * @var T4
            */
            return static::DaftRouterHandleRequestWithTypedArgs($request, $args);
        }

        /**
        * @var T3
        */
        return static::DaftRouterHandleRequestWithEmptyArgs($request);
    }

    /**
    * @param T2 $args
    *
    * @return T3
    */
    abstract public static function DaftRouterHandleRequestWithEmptyArgs(
        Request $request
    ) : Response;

    /**
    * @param T2 $args
    *
    * @return T4
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
