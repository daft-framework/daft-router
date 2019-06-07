<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftRouter;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
* @template ARGS as array<string, string>
* @template TYPED as array<string, scalar>
*/
interface DaftRoute
{
    /**
    * @param array<string, string> $args
    *
    * @psalm-param ARGS $args
    */
    public static function DaftRouterHandleRequest(Request $request, array $args) : Response;

    /**
    * @return array<string, array<int, string>> an array of URIs & methods
    */
    public static function DaftRouterRoutes() : array;

    /**
    * @param array<string, string> $args
    *
    * @psalm-param ARGS $args
    *
    * @throws \InvalidArgumentException if no uri could be found
    */
    public static function DaftRouterHttpRoute(array $args, string $method = 'GET') : string;

    /**
    * @param array<string, string> $args
    *
    * @psalm-param ARGS $args
    *
    * @throws \InvalidArgumentException if $args or $method are not supported or invalid
    *
    * @return array<string, string>
    *
    * @psalm-return ARGS
    */
    public static function DaftRouterHttpRouteArgs(array $args, string $method) : array;

    /**
    * @param array<string, string> $args
    *
    * @psalm-param ARGS $args
    *
    * @throws \InvalidArgumentException if $args or $method are not supported or invalid
    *
    * @return array<string, mixed>
    *
    * @psalm-return TYPED
    */
    public static function DaftRouterHttpRouteArgsTyped(array $args, string $method) : array;
}
