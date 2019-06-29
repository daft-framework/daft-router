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
* @psalm-type T1 = array<empty, empty>
* @psalm-type T2 = EmptyArgs
* @template T3 as Response
*
* @template-implements DaftRouteAcceptsEmptyArgs<T1, T1, TypedArgs, T3>
*/
abstract class DaftRouteAcceptsOnlyEmptyArgs implements DaftRouteAcceptsEmptyArgs
{
    use DaftRouterAutoMethodCheckingTrait;

    /**
    * @deprecated
    *
    * @param EmptyArgs $args
    *
    * @return T3
    */
    final public static function DaftRouterHandleRequest(Request $request, $args) : Response
    {
        static::DaftRouterAutoMethodChecking($request->getMethod());

        /**
        * @var T3
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
    */
    final public static function DaftRouterHttpRouteArgsTyped(
        array $args,
        string $method
    ) : EmptyArgs {
        static::DaftRouterAutoMethodChecking($method);

        return new EmptyArgs();
    }
}
