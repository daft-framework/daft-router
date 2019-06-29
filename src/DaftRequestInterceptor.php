<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftRouter;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
* @template T1 as Response
* @template T2 as Response
*/
interface DaftRequestInterceptor extends DaftRouteFilter
{
    /**
    * @param T1|null $response
    *
    * @return T2|null
    */
    public static function DaftRouterMiddlewareHandler(
        Request $request,
        ? Response $response
    ) : ? Response;
}
