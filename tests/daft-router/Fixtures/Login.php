<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftRouter\Tests\Fixtures;

use InvalidArgumentException;
use SignpostMarv\DaftRouter\DaftRoute;
use SignpostMarv\DaftRouter\DaftRouterAutoMethodCheckingTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
* @template ARGS as array{mode:'admin'}|array<empty, empty>
*
* @template-implements DaftRoute<ARGS, ARGS>
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

    public static function DaftRouterHttpRouteArgs(array $args, string $method) : array
    {
        static::DaftRouterAutoMethodChecking($method);

        return $args;
    }

    public static function DaftRouterHttpRouteArgsTyped(array $args, string $method) : array
    {
        static::DaftRouterAutoMethodChecking($method);

        return static::DaftRouterHttpRouteArgs($args, $method);
    }

    public static function DaftRouterHttpRoute(array $args, string $method = 'GET') : string
    {
        $args = static::DaftRouterHttpRouteArgsTyped($args, $method);

        return ('admin' === ($args['mode'] ?? null)) ? '/admin/login' : '/login';
    }

    public static function DaftRouterHandleRequest(Request $request, array $args) : Response
    {
        return new Response('');
    }
}
