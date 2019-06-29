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
interface DaftResponseModifier extends DaftRouteFilter
{
    /**
    * @param T1 $response
    *
    * @return T2
    */
    public static function DaftRouterMiddlewareModifier(
        Request $request,
        Response $response
    ) : Response;
}
