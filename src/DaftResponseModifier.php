<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftRouter;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

interface DaftResponseModifier extends DaftRouteFilter
{
    public static function DaftRouterMiddlewareModifier(
        Request $request,
        Response $response
    ) : Response;
}
