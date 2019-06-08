<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftRouter\Tests\Fixtures;

use InvalidArgumentException;
use SignpostMarv\DaftRouter\DaftRoute;
use SignpostMarv\DaftRouter\DaftRouterAutoMethodCheckingTrait;
use SignpostMarv\DaftRouter\EmptyArgs;
use SignpostMarv\DaftRouter\TypedArgs;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
* @template T1 as array<empty, empty>|array{mode:'admin'}
* @template T2 as EmptyArgs|AdminModeArgs
*
* @template-implements DaftRoute<T1, T2>
*/
class Login implements DaftRoute
{
    use DaftRouterAutoMethodCheckingTrait;

    public static function DaftRouterRoutes() : array
    {
        return [
            '/login' => ['GET', 'POST'],
            '/{mode:admin}/login' => ['GET', 'POST'],
        ];
    }

    public static function DaftRouterHttpRouteArgsTyped(array $args, string $method) : TypedArgs
    {
        /**
        * @var T2
        */
        $out = new EmptyArgs();

        if ('admin' === ($args['mode'] ?? null)) {
            $out = new AdminModeArgs();
        }

        return $out;
    }

    /**
    * @param T2 $args
    */
    public static function DaftRouterHttpRoute(TypedArgs $args, string $method = 'GET') : string
    {
        static::DaftRouterAutoMethodChecking($method);

        return (0 === count($args)) ? '/login' : '/admin/login';
    }

    /**
    * @param T2 $args
    */
    public static function DaftRouterHandleRequest(Request $request, TypedArgs $args) : Response
    {
        return new Response('');
    }
}
