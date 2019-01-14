<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftRouter;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

interface DaftRequestInterceptor extends DaftRouteFilter
{
    /**
    * @return Response|null
    */
    public static function DaftRouterMiddlewareHandler(
        Request $request,
        Response $response = null
    );
}
