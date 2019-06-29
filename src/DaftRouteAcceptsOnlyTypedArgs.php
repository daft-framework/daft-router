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
* @template-implements DaftRouteAcceptsTypedArgs<T1, T2>
*/
abstract class DaftRouteAcceptsOnlyTypedArgs implements DaftRouteAcceptsTypedArgs
{
    use DaftRouterAutoMethodCheckingTrait;

    /**
    * @deprecated
    *
    * @param T2 $args
    */
    final public static function DaftRouterHandleRequest(Request $request, $args) : Response
    {
        static::DaftRouterAutoMethodChecking($request->getMethod());

        return static::DaftRouterHandleRequestWithTypedArgs($request, $args);
    }

    /**
    * @param T2 $args
    */
    abstract public static function DaftRouterHandleRequestWithTypedArgs(
        Request $request,
        TypedArgs $args
    ) : Response;

    /**
    * @param T2 $args
    *
    * @throws \InvalidArgumentException if no uri could be found
    */
    final public static function DaftRouterHttpRoute($args, string $method = 'GET') : string
    {
        static::DaftRouterAutoMethodChecking($method);

        return static::DaftRouterHttpRouteWithTypedArgs($args, $method);
    }

    /**
    * @param T2 $args
    *
    * @throws \InvalidArgumentException if no uri could be found
    */
    abstract public static function DaftRouterHttpRouteWithTypedArgs(
        TypedArgs $args,
        string $method = 'GET'
    ) : string;
}
