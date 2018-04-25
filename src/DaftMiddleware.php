<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftRouter;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

interface DaftMiddleware
{
    public static function DaftRouterMiddlewareHandler(
        Request $request,
        ? Response $response
    ) : ? Response;

    /**
    * @return array<int, string> URI prefixes
    */
    public static function DaftRouterRoutePrefixExceptions() : array;
}
