<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftRouter\Tests\Fixtures;

use InvalidArgumentException;
use SignpostMarv\DaftRouter\DaftRouteAcceptsOnlyTypedArgs;
use SignpostMarv\DaftRouter\DaftRouterHttpRouteDefaultMethodGet;
use SignpostMarv\DaftRouter\TypedArgs;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
* @psalm-type T = array{locator:string}
* @psalm-type TYPED = LocatorArgs
* @psalm-type R = Response
*
* @template-extends DaftRouteAcceptsOnlyTypedArgs<T, T, TYPED, R, 'GET', 'GET'>
*/
class Content extends DaftRouteAcceptsOnlyTypedArgs
{
    use DaftRouterHttpRouteDefaultMethodGet;

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
        string $method = null
    ) : string {
        return $args->locator;
    }

    /**
    * @param T $args
    * @param 'GET'|null $method
    *
    * @return TYPED
    */
    public static function DaftRouterHttpRouteArgsTyped(
        array $args,
        string $method = null
    ) : TypedArgs {
        /**
        * @var T
        */
        $args = $args;

        return new LocatorArgs($args);
    }
}
