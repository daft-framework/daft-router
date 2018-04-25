<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftRouter\Tests\Fixtures;

use InvalidArgumentException;
use SignpostMarv\DaftRouter\DaftRoute;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class Home implements DaftRoute
{
    public static function DaftRouterHandleRequest(Request $request, array $args) : Response
    {
        return new Response('');
    }

    public static function DaftRouterRoutes() : array
    {
        return [
            '/' => ['GET'],
        ];
    }

    public static function DaftRouterHttpRoute(array $args, string $method = 'GET') : string
    {
        if ( ! empty($args)) {
            throw new InvalidArgumentException('This route takes no arguments!');
        }

        return '/';
    }
}
