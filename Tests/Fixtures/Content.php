<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftRouter\Tests\Fixtures;

use InvalidArgumentException;
use SignpostMarv\DaftRouter\DaftRoute;
use SignpostMarv\DaftRouter\DaftRouterAutoMethodCheckingTrait;
use SignpostMarv\DaftRouter\TypedArgs;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
* @template T as array{locator:string}
* @template TYPED as LocatorArgs
*
* @template-implements DaftRoute<T, TYPED>
*/
class Content implements DaftRoute
{
    use DaftRouterAutoMethodCheckingTrait;

    const MIN_EXPECTED_ARGS = 1;

    const MAX_EXPECTED_ARGS = 2;

    /**
    * @param TYPED $args
    */
    public static function DaftRouterHandleRequest(Request $request, TypedArgs $args) : Response
    {
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
    public static function DaftRouterHttpRoute(TypedArgs $args, string $method = 'GET') : string
    {
        static::DaftRouterAutoMethodChecking($method);

        return $args->locator;
    }

    /**
    * @param T $args
    */
    public static function DaftRouterHttpRouteArgsTyped(array $args, string $method) : TypedArgs
    {
        static::DaftRouterAutoMethodChecking($method);

        return new LocatorArgs($args);
    }
}
