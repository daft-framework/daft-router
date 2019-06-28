<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftRouter\Tests\Fixtures;

use InvalidArgumentException;
use SignpostMarv\DaftRouter\DaftRoute;
use SignpostMarv\DaftRouter\DaftRouteEmptyArgs;
use SignpostMarv\DaftRouter\EmptyArgs;
use SignpostMarv\DaftRouter\TypedArgs;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class Home extends DaftRouteEmptyArgs
{
    public static function DaftRouterHandleRequestWithEmptyArgs(Request $request) : Response
    {
        return new Response('');
    }

    public static function DaftRouterRoutes() : array
    {
        return [
            '/' => ['GET'],
        ];
    }

    public static function DaftRouterHttpRouteWithEmptyArgs(string $method = 'GET') : string
    {
        return '/';
    }
}
