<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftRouter;

use DateTimeImmutable;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
* @template T1 as array<string, scalar|DateTimeImmutable|null>
* @template T1_STRINGS as array<string, string|null>
* @template T2 as TypedArgs
* @template T3 as Response
*
* @template-extends DaftRoute<T1, T1_STRINGS, T2, T3, T3>
*/
interface DaftRouteAcceptsTypedArgs extends DaftRoute
{
    /**
    * @param T2 $args
    *
    * @return T3
    */
    public static function DaftRouterHandleRequestWithTypedArgs(
        Request $request,
        TypedArgs $args
    ) : Response;

    /**
    * @param T1_STRINGS|array<empty, empty> $args
    *
    * @return T2|EmptyArgs
    */
    public static function DaftRouterHttpRouteArgsTyped(array $args, string $method);

    /**
    * @param T2 $args
    *
    * @throws \InvalidArgumentException if no uri could be found
    */
    public static function DaftRouterHttpRouteWithTypedArgs(
        TypedArgs $args,
        string $method = 'GET'
    ) : string;
}
