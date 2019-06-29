<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftRouter\Tests\Fixtures;

use InvalidArgumentException;
use SignpostMarv\DaftRouter\DaftRouteAcceptsOnlyTypedArgs;
use SignpostMarv\DaftRouter\DaftRouterAutoMethodCheckingTrait;
use SignpostMarv\DaftRouter\TypedArgs;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
* @psalm-type T = array{locator:string}
* @psalm-type TYPED = LocatorArgs
* @psalm-type R = Response
*
* @template-extends DaftRouteAcceptsOnlyTypedArgs<T, T, TYPED, R>
*/
class Content extends DaftRouteAcceptsOnlyTypedArgs
{
    use DaftRouterAutoMethodCheckingTrait;

    const MIN_EXPECTED_ARGS = 1;

    const MAX_EXPECTED_ARGS = 2;

    /**
    * @param TYPED $args
    */
    public static function DaftRouterHandleRequestWithTypedArgs(
        Request $request,
        TypedArgs $args
    ) : Response {
        return new Response('');
    }

    public static function DaftRouterRoutes() : array
    {
        return [
            '{locator:/.+}' => ['GET'],
        ];
    }

    /**
    * @param TYPED $args
    */
    public static function DaftRouterHttpRouteWithTypedArgs(
        TypedArgs $args,
        string $method = 'GET'
    ) : string {
        return $args->locator;
    }

    /**
    * @param T $args
    *
    * @return TYPED
    */
    public static function DaftRouterHttpRouteArgsTyped(array $args, string $method) : TypedArgs
    {
        static::DaftRouterAutoMethodChecking($method);

        /**
        * @var T
        */
        $args = $args;

        return new LocatorArgs($args);
    }
}
