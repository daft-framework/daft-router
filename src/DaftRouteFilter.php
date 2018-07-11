<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftRouter;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

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
