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
* @template-extends DaftRoute<T1, T1_STRINGS, T2|EmptyArgs, T3, T3>
*/
interface DaftRouteAcceptsEmptyArgs extends DaftRoute
{
    /**
    * @return T3
    */
    public static function DaftRouterHandleRequestWithEmptyArgs(Request $request) : Response;

    /**
    * @throws \InvalidArgumentException if no uri could be found
    */
    public static function DaftRouterHttpRouteWithEmptyArgs(string $method = 'GET') : string;
}
