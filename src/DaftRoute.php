<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftRouter;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

interface DaftRoute
{
    public static function DaftRouterHandleRequest(Request $request, array $args) : Response;

    /**
    * @return array<string, array<int, string>> an array of URIs & methods
    */
    public static function DaftRouterRoutes() : array;

    /**
    * @param array<string, string> $args
    *
    * @throws \InvalidArgumentException if no uri could be found
    */
    public static function DaftRouterHttpRoute(array $args, string $method = 'GET') : string;

    /**
    * @param array<string, string> $args
    *
    * @return array<string, string>
    *
    * @throws \InvalidArgumentException if $args or $method are not supported or invalid
    */
    public static function DaftRouterHttpRouteArgs(array $args, string $method) : array;

    /**
    * @param array<string, string> $args
    *
    * @return array<string, mixed>
    *
    * @throws \InvalidArgumentException if $args or $method are not supported or invalid
    */
    public static function DaftRouterHttpRouteArgsTyped(array $args, string $method) : array;
}
