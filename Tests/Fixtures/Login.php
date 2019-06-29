<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftRouter\Tests\Fixtures;

use InvalidArgumentException;
use SignpostMarv\DaftRouter\DaftRouteAcceptsBothEmptyAndTypedArgs;
use SignpostMarv\DaftRouter\DaftRouterAutoMethodCheckingTrait;
use SignpostMarv\DaftRouter\EmptyArgs;
use SignpostMarv\DaftRouter\TypedArgs;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
* @psalm-type T1 = array{mode:'admin'}
* @psalm-type T2 = AdminModeArgs
* @psalm-type T3 = Response
*
* @template-extends DaftRouteAcceptsBothEmptyAndTypedArgs<T1, T2, T3, T3>
*/
class Login extends DaftRouteAcceptsBothEmptyAndTypedArgs
{
    public static function DaftRouterRoutes() : array
    {
        return [
            '/login' => ['GET', 'POST'],
            '/{mode:admin}/login' => ['GET', 'POST'],
        ];
    }

    /**
    * @return T2|EmptyArgs
    */
    public static function DaftRouterHttpRouteArgsTyped(array $args, string $method)
    {
        if ('admin' === ($args['mode'] ?? null)) {
            return new AdminModeArgs();
        }

        return new EmptyArgs();
    }

    /**
    * @param T2 $args
    */
    public static function DaftRouterHttpRouteWithEmptyArgs(string $method = 'GET') : string
    {
        static::DaftRouterAutoMethodChecking($method);

        return '/login';
    }

    /**
    * @param T2 $args
    */
    public static function DaftRouterHttpRouteWithTypedArgs(
        TypedArgs $args,
        string $method = 'GET'
    ) : string {
        static::DaftRouterAutoMethodChecking($method);

        return '/admin/login';
    }

    /**
    * @param T2 $args
    */
    public static function DaftRouterHandleRequestWithTypedArgs(
        Request $request,
        TypedArgs $args
    ) : Response {
        static::DaftRouterAutoMethodChecking($request->getMethod());

        return new Response('');
    }

    /**
    * @param T2 $args
    */
    public static function DaftRouterHandleRequestWithEmptyArgs(Request $request) : Response
    {
        static::DaftRouterAutoMethodChecking($request->getMethod());

        return new Response('');
    }
}
