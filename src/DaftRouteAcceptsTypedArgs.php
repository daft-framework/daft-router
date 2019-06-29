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
* @template T1 as array<string, scalar|DateTimeImmutable|null>|array<empty, empty>
* @template T1_STRINGS as array<string, string|null>|array<empty, empty>
* @template T2 as TypedArgs
* @template T3 as Response
*
* @template-extends DaftRoute<T1|array<empty, empty>, T1_STRINGS|array<empty, empty>, T2|EmptyArgs, T3, T3>
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
    * @param T1_STRINGS $args
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