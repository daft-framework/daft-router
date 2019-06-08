<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftRouter;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
* @template ARRAY as array<string, scalar>
* @template TYPED as TypedArgs
*/
interface DaftRoute
{
    /**
    * @param TYPED $args
    */
    public static function DaftRouterHandleRequest(Request $request, TypedArgs $args) : Response;

    /**
    * @return array<string, array<int, string>> an array of URIs & methods
    */
    public static function DaftRouterRoutes() : array;

    /**
    * @param TYPED $args
    *
    * @throws \InvalidArgumentException if no uri could be found
    */
    public static function DaftRouterHttpRoute(TypedArgs $args, string $method = 'GET') : string;

    /**
    * @param ARRAY $args
    *
    * @return TYPED
    */
    public static function DaftRouterHttpRouteArgsTyped(array $args, string $method) : TypedArgs;
}
