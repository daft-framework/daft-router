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
* @template T3 as Response
*
* @template-implements DaftRouteAcceptsTypedArgs<T1, T1_STRINGS, T2, T3>
*/
abstract class DaftRouteAcceptsOnlyTypedArgs implements DaftRouteAcceptsTypedArgs
{
    use DaftRouterAutoMethodCheckingTrait;

    /**
    * @deprecated
    *
    * @param T2 $args
    *
    * @return T3
    */
    final public static function DaftRouterHandleRequest(Request $request, $args) : Response
    {
        static::DaftRouterAutoMethodChecking($request->getMethod());

        /**
        * @var T3
        */
        return static::DaftRouterHandleRequestWithTypedArgs($request, $args);
    }

    /**
    * @param T2 $args
    *
    * @return T3
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
