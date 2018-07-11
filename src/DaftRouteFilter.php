<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftRouter;

interface DaftRouteFilter
{
    /**
    * @return array<int, string> URI prefixes
    */
    public static function DaftRouterRoutePrefixExceptions() : array;

    /**
    * @return array<int, string> URI prefixes
    */
    public static function DaftRouterRoutePrefixRequirements() : array;
}
