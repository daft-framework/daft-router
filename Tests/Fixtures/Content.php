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
use SignpostMarv\DaftRouter\TypedArgsInterface;
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

    public static function DaftRouterHandleRequest(Request $request, TypedArgsInterface $args) : Response
    {
        return new Response('');
    }

    public static function DaftRouterRoutes() : array
    {
        return [
            '{locator:/.+}' => ['GET'],
        ];
    }

    public static function DaftRouterHttpRoute(TypedArgsInterface $args, string $method = 'GET') : string
    {
        static::DaftRouterAutoMethodChecking($method);

        return $args->locator;
    }

    /**
    * @param T $args
    */
    public static function DaftRouterHttpRouteArgsTyped(array $args, string $method) : TypedArgsInterface
    {
        static::DaftRouterAutoMethodChecking($method);

        /**
        * @var T
        */
        $args = $args;

        return new LocatorArgs($args);
    }
}
