<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftRouter\Tests\Fixtures;

use InvalidArgumentException;
use SignpostMarv\DaftRouter\DaftRoute;
use SignpostMarv\DaftRouter\DaftRouterAutoMethodCheckingTrait;
use SignpostMarv\DaftRouter\TypedArgs;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
* @template T as array{id:int}|array{id:int, slug:string}
* @template TYPED as IntIdArgs|IntIdStringSlugArgs
*
* @template-implements DaftRoute<T, TYPED>
*/
class Profile implements DaftRoute
{
    use DaftRouterAutoMethodCheckingTrait;

    const MIN_EXPECTED_ARGS = 1;

    const MAX_EXPECTED_ARGS = 2;

    /**
    * @param TYPED $args
    */
    public static function DaftRouterHandleRequest(Request $request, TypedArgs $args) : Response
    {
        return new Response('');
    }

    public static function DaftRouterRoutes() : array
    {
        return [
            '/profile/{id:\d+}[~{slug:[^\/]+}]' => ['GET'],
        ];
    }

    /**
    * @param TYPED $args
    */
    public static function DaftRouterHttpRoute(TypedArgs $args, string $method = 'GET') : string
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

    public static function DaftRouterHttpRouteArgsTyped(array $args, string $method) : TypedArgs
    {
        if (isset($args['slug'])) {
            /**
            * @var array{id:string, slug:string}
            */
            $args = $args;

            return new IntIdStringSlugArgs($args);
        }

        /**
        * @var array{id:string}
        */
        $args = $args;

        return new IntIdArgs($args);
    }
}
