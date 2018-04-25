<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftRouter\Tests\Fixtures;

use SignpostMarv\DaftRouter\DaftMiddleware;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class NotLoggedIn implements DaftMiddleware
{
    public static function DaftRouterMiddlewareHandler(
        Request $request,
        ? Response $response
    ) : ? Response {
        if ( ! ($response instanceof Response) && ! $request->query->has('loggedin')) {
            return new RedirectResponse('/login');
        }

        return $response;
    }

    /**
    * @return array<int, string> URI prefixes
    */
    public static function DaftRouterRoutePrefixExceptions() : array
    {
        return [
            '/login',
        ];
    }
}
