<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftRouter\Middleware;

use InvalidArgumentException;
use SignpostMarv\DaftRouter\DaftMiddleware;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class Handler
{
    public static function ProcessRequest(
        Request $request,
        string ...$maybeMiddleware
    ) : ? Response {
        $middleware = [];

        $out = null;

        foreach ($maybeMiddleware as $i => $maybe) {
            if ( ! is_a($maybe, DaftMiddleware::class, true)) {
                throw new InvalidArgumentException(sprintf(
                    'Argument %s passed to %s must be an implementation of %s',
                    $i + 1,
                    __METHOD__,
                    DaftMiddleware::class
                ));
            }

            $middleware[] = $maybe;
        }

        foreach ($middleware as $definitely) {
            $out = $definitely::DaftRouterMiddlewareHandler($request, $out);
        }

        return $out;
    }
}
