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
        $args = static::DaftRouterHttpRouteArgsTyped($args, $method);

        return
            '/profile/' .
            rawurlencode((string) $args['id']) .
            (
                isset($args['slug'])
                    ? ('~' . rawurlencode((string) $args['slug']))
                    : ''
            );
    }

    /**
    * @return array<string, string>
    *
    * @psalm-return array{id:string, slug?:string}
    */
    public static function DaftRouterHttpRouteArgs(array $args, string $method) : array
    {
        $count = count($args);

        /**
        * @var array<string, string>
        */
        $args = array_filter(array_filter($args, 'is_string'), 'is_string', ARRAY_FILTER_USE_KEY);

        if (count($args) !== $count) {
            throw new InvalidArgumentException(
                'Argument 1 passed to ' .
                __METHOD__ .
                ' must be array<string, string>'
            );
        } elseif ($count < self::MIN_EXPECTED_ARGS || $count > self::MAX_EXPECTED_ARGS) {
            throw new InvalidArgumentException(sprintf(
                'This route accepts either %u or %u args',
                self::MIN_EXPECTED_ARGS,
                self::MAX_EXPECTED_ARGS
            ));
        } elseif ( ! isset($args['id']) || ! ctype_digit($args['id'])) {
            throw new InvalidArgumentException('id argument not specified correctly!');
        } elseif (isset($args['slug']) && rawurlencode($args['slug']) !== $args['slug']) {
            throw new InvalidArgumentException(
                'slug argument specified but not specified correctly!'
            );
        }

        $out = [
            'id' => $args['id'],
        ];

        if (isset($args['slug'])) {
            $out['slug'] = $args['slug'];
        }

        return $out;
    }

    /**
    * @return array<string, scalar>
    *
    * @psalm-return array{id:int, slug?:string}
    */
    public static function DaftRouterHttpRouteArgsTyped(array $args, string $method) : array
    {
        $args = static::DaftRouterHttpRouteArgs($args, $method);

        $args['id'] = (int) $args['id'];

        return $args;
    }
}
