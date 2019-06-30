<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftRouter;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
* @psalm-type T1 = array<empty, empty>
* @template R_EMPTY as Response
*
* @template-implements DaftRouteAcceptsEmptyArgs<T1, T1, TypedArgs, R_EMPTY, Response>
*/
abstract class DaftRouteAcceptsOnlyEmptyArgs implements DaftRouteAcceptsEmptyArgs
{
    use DaftRouterAutoMethodCheckingTrait;

    /**
    * @deprecated
    *
    * @param EmptyArgs $args
    *
    * @return R_EMPTY
    */
    final public static function DaftRouterHandleRequest(Request $request, $args) : Response
    {
        static::DaftRouterAutoMethodChecking($request->getMethod());

        /**
        * @var R_EMPTY
        */
        return static::DaftRouterHandleRequestWithEmptyArgs($request);
    }

    /**
    * @deprecated
    *
    * @param EmptyArgs $args
    */
    final public static function DaftRouterHttpRoute(
        $args,
        string $method = 'GET'
    ) : string {
        static::DaftRouterAutoMethodChecking($method);

        return static::DaftRouterHttpRouteWithEmptyArgs($method);
    }

    /**
    * @param T1 $args
    *
    * @return EmptyArgs
    */
    final public static function DaftRouterHttpRouteArgsTyped(
        array $args,
        string $method
    ) {
        static::DaftRouterAutoMethodChecking($method);

        return new EmptyArgs();
    }
}
