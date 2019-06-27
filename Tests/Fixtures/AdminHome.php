<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftRouter\Tests\Fixtures;

use InvalidArgumentException;
use SignpostMarv\DaftRouter\DaftRoute;
use SignpostMarv\DaftRouter\DaftRouterZeroArgumentsTrait;
use SignpostMarv\DaftRouter\EmptyArgs;
use SignpostMarv\DaftRouter\TypedArgs;
use SignpostMarv\DaftRouter\TypedArgsInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
* @template-implements DaftRoute<array<empty, empty>, EmptyArgs>
*/
class AdminHome implements DaftRoute
{
    use DaftRouterZeroArgumentsTrait;

    public static function DaftRouterHandleRequest(Request $request, TypedArgsInterface $args) : Response
    {
        return new Response('');
    }

    public static function DaftRouterRoutes() : array
    {
        return [
            '/admin' => ['GET'],
        ];
    }

    public static function DaftRouterHttpRoute(TypedArgsInterface $args, string $method = 'GET') : string
    {
        return '/admin';
    }
}
