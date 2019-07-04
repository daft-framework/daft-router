<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftRouter\Tests\Fixtures;

use InvalidArgumentException;
use SignpostMarv\DaftRouter\DaftRouteAcceptsBothEmptyAndTypedArgs;
use SignpostMarv\DaftRouter\DaftRouterAutoMethodCheckingTrait;
use SignpostMarv\DaftRouter\DaftRouterHttpRouteDefaultMethodGet;
use SignpostMarv\DaftRouter\EmptyArgs;
use SignpostMarv\DaftRouter\TypedArgs;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
* @psalm-type T1 = array{mode:'admin'}
* @psalm-type T2 = AdminModeArgs
* @psalm-type T3 = Response
* @psalm-type HTTP_METHOD = 'GET'|'POST'
*
* @template-extends DaftRouteAcceptsBothEmptyAndTypedArgs<T1, T1, T2, T3, T3, HTTP_METHOD, HTTP_METHOD, 'GET'>
*/
class Login extends DaftRouteAcceptsBothEmptyAndTypedArgs
{
    use DaftRouterHttpRouteDefaultMethodGet;

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
    public static function DaftRouterHttpRouteArgsTyped(array $args, string $method = null)
    {
        if ('admin' === ($args['mode'] ?? null)) {
            return new AdminModeArgs();
        }

        return new EmptyArgs();
    }

    /**
    * @param T2 $args
    */
    public static function DaftRouterHttpRouteWithEmptyArgs(string $method = null) : string
    {
        return '/login';
    }

    /**
    * @param T2 $args
    */
    public static function DaftRouterHttpRouteWithTypedArgs(
        TypedArgs $args,
        string $method = null
    ) : string {
        return '/admin/login';
    }

    /**
    * @param T2 $args
    */
    public static function DaftRouterHandleRequestWithTypedArgs(
        Request $request,
        TypedArgs $args
    ) : Response {
        /**
        * @var 'GET'|'POST'
        */
        $method = $request->getMethod();
        static::DaftRouterAutoMethodChecking($method);

        return new Response('');
    }

    /**
    * @param T2 $args
    */
    public static function DaftRouterHandleRequestWithEmptyArgs(Request $request) : Response
    {
        /**
        * @var 'GET'|'POST'
        */
        $method = $request->getMethod();
        static::DaftRouterAutoMethodChecking($method);

        return new Response('');
    }
}
