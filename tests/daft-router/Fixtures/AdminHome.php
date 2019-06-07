<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftRouter\Tests\Fixtures;

use InvalidArgumentException;
use SignpostMarv\DaftRouter\DaftRoute;
use SignpostMarv\DaftRouter\DaftRouterZeroArgumentsTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
* @template T as array<empty, empty>
*
* @template-implements DaftRoute<T>
*/
class AdminHome implements DaftRoute
{
    use DaftRouterZeroArgumentsTrait;

    /**
    * @psalm-param T $args
    */
    public static function DaftRouterHandleRequest(Request $request, array $args) : Response
    {
        return new Response('');
    }

    public static function DaftRouterRoutes() : array
    {
        return [
            '/admin' => ['GET'],
        ];
    }

    /**
    * @psalm-param T $args
    */
    public static function DaftRouterHttpRoute(array $args, string $method = 'GET') : string
    {
        return '/admin';
    }
}
