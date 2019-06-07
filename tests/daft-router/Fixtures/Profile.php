<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftRouter\Tests\Fixtures;

use InvalidArgumentException;
use SignpostMarv\DaftRouter\DaftRoute;
use SignpostMarv\DaftRouter\DaftRouterAutoMethodCheckingTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
* @template T as array{id:int, slug?:string}
*
* @template-implements DaftRoute<T>
*/
class Profile implements DaftRoute
{
    use DaftRouterAutoMethodCheckingTrait;

    const MIN_EXPECTED_ARGS = 1;

    const MAX_EXPECTED_ARGS = 2;

    public static function DaftRouterHandleRequest(Request $request, array $args) : Response
    {
        return new Response('');
    }

    public static function DaftRouterRoutes() : array
    {
        return [
            '/profile/{id:\d+}[~{slug:[^\/]+}]' => ['GET'],
        ];
    }

    public static function DaftRouterHttpRoute(array $args, string $method = 'GET') : string
    {
        $slug = $args['slug'] ?? null;

        if (is_string($slug)) {
            return
                '/profile/' .
                rawurlencode((string) $args['id']) .
                '~' .
                rawurlencode($slug);
        }

        return '/profile/' . rawurlencode((string) $args['id']);
    }

    public static function DaftRouterHttpRouteArgsTyped(array $args, string $method) : array
    {
        /**
        * @psalm-var T
        */
        $out = [
            'id' => (int) $args['id'],
        ];

        if (isset($args['slug'])) {
            $out['slug'] = $args['slug'];
        }

        return $out;
    }
}
