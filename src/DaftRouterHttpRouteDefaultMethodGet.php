<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftRouter;

/**
* @psalm-type HTTP_METHOD_DEFAULT = 'GET'|'GET'
*/
trait DaftRouterHttpRouteDefaultMethodGet
{
    /**
    * @return HTTP_METHOD_DEFAULT
    */
    public static function DaftRouterHttpRouteDefaultMethod() : string
    {
        return 'GET';
    }
}
