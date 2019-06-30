<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftRouter\Tests\Fixtures;

use InvalidArgumentException;
use SignpostMarv\DaftRouter\DaftRoute;
use SignpostMarv\DaftRouter\DaftRouteAcceptsOnlyTypedArgs;
use SignpostMarv\DaftRouter\DaftRouterAutoMethodCheckingTrait;
use SignpostMarv\DaftRouter\TypedArgs;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
* @psalm-type SLUG = array{id:int, slug:string}
* @psalm-type SANS_SLUG = array{id:int}
* @psalm-type S_SLUG = array{id:string, slug:string}
* @psalm-type S_SANS_SLUG = array{id:string}
* @psalm-type R = Response
*
* @template-extends DaftRouteAcceptsOnlyTypedArgs<SLUG|SANS_SLUG, S_SLUG|S_SANS_SLUG, IntIdArgs|IntIdStringSlugArgs, R>
*/
class Profile extends DaftRouteAcceptsOnlyTypedArgs
{
    use DaftRouterAutoMethodCheckingTrait;

    const MIN_EXPECTED_ARGS = 1;

    const MAX_EXPECTED_ARGS = 2;

    /**
    * @param IntIdArgs|IntIdStringSlugArgs $args
    */
    public static function DaftRouterHandleRequestWithTypedArgs(
        Request $request,
        TypedArgs $args
    ) : Response {
        static::DaftRouterAutoMethodChecking($request->getMethod());

        return new Response('');
    }

    public static function DaftRouterRoutes() : array
    {
        return [
            '/profile/{id:\d+}[~{slug:[^\/]+}]' => ['GET'],
        ];
    }

    /**
    * @param IntIdArgs|IntIdStringSlugArgs $args
    */
    public static function DaftRouterHttpRouteWithTypedArgs(TypedArgs $args, string $method = 'GET') : string
    {
        static::DaftRouterAutoMethodChecking($method);

        if ($args instanceof IntIdStringSlugArgs) {
            return
                '/profile/' .
                rawurlencode((string) $args->id) .
                '~' .
                rawurlencode($args->slug);
        }

        /**
        * @var IntIdArgs
        */
        $args = $args;

        return
            '/profile/' .
            rawurlencode((string) $args->id);
    }

    /**
    * @param S_SLUG|S_SANS_SLUG $args
    *
    * @return IntIdArgs|IntIdStringSlugArgs
    */
    public static function DaftRouterHttpRouteArgsTyped(array $args, string $method)
    {
        if (isset($args['slug'])) {
            return new IntIdStringSlugArgs($args);
        }

        return new IntIdArgs($args);
    }
}
