<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftRouter;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

trait DaftRouterZeroArgumentsTrait
{
    /**
    * @param EmptyArgs $args
    */
    abstract public static function DaftRouterHandleRequest(
        Request $request,
        $args
    ) : Response;

    /**
    * @param EmptyArgs $args
    */
    abstract public static function DaftRouterHttpRoute(
        $args,
        string $method = 'GET'
    ) : string;

    /**
    * @param array<empty, empty> $args
    *
    * @return EmptyArgs
    */
    public static function DaftRouterHttpRouteArgsTyped(array $args, string $method)
    {
        /**
        * @var EmptyArgs
        */
        return new EmptyArgs();
    }
}
