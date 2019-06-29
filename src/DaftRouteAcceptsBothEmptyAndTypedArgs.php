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
*
* @template-implements DaftRouteAcceptsEmptyArgs<T1, T2>
* @template-implements DaftRouteAcceptsTypedArgs<T1, T2>
*/
abstract class DaftRouteAcceptsBothEmptyAndTypedArgs implements DaftRouteAcceptsEmptyArgs, DaftRouteAcceptsTypedArgs
{
    use DaftRouterAutoMethodCheckingTrait;

    /**
    * @param T2|EmptyArgs $args
    */
    final public static function DaftRouterHandleRequest(
        Request $request,
        $args
    ) : Response {
        static::DaftRouterAutoMethodChecking($request->getMethod());

        if ($args instanceof TypedArgs) {
            return static::DaftRouterHandleRequestWithTypedArgs($request, $args);
        }

        return static::DaftRouterHandleRequestWithEmptyArgs($request);
    }

    /**
    * @param T2 $args
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
