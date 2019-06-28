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
* @psalm-type T = array{locator:string}
* @psalm-type TYPED = LocatorArgs
*
* @template-implements DaftRoute<T, TYPED>
*/
class Content implements DaftRoute
{
    use DaftRouterAutoMethodCheckingTrait;

    const MIN_EXPECTED_ARGS = 1;

    const MAX_EXPECTED_ARGS = 2;

    /**
    * @param LocatorArgs $args
    */
    public static function DaftRouterHandleRequest(Request $request, $args) : Response
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
    public static function DaftRouterHttpRoute($args, string $method = 'GET') : string
    {
        static::DaftRouterAutoMethodChecking($method);

        return $args->locator;
    }

    /**
    * @param array{locator:string} $args
    *
    * @return TYPED
    */
    public static function DaftRouterHttpRouteArgsTyped(array $args, string $method)
    {
        static::DaftRouterAutoMethodChecking($method);

        /**
        * @var T
        */
        $args = $args;

        return new LocatorArgs($args);
    }
}
