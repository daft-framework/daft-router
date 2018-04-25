<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftRouter\Tests\Fixtures;

use SignpostMarv\DaftRouter\DaftMiddleware;
use Symfony\Component\HttpFoundation\{
    RedirectResponse,
    Request,
    Response
};

class NotLoggedIn implements DaftMiddleware
{
    public static function DaftRouterMiddlewareHandler(
        Request $request,
        ? Response $response
    ) : ? Response {
        if ( ! ($response instanceof Response)) {
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
