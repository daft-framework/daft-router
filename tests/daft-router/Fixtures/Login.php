<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftRouter\Tests\Fixtures;

use InvalidArgumentException;

class Login extends Home
{
    public static function DaftRouterRoutes() : array
    {
        return [
            '/login' => ['GET', 'POST'],
            '/{mode:admin}/login' => ['GET', 'POST'],
        ];
    }

    /**
    * @return array<string, string>
    */
    public static function DaftRouterHttpRouteArgs(array $args, string $method) : array
    {
        static::DaftRouterAutoMethodChecking($method);

        $count = count($args);

        if (0 === $count) {
            return [];
        } elseif (1 === $count) {
            if ( ! isset($args['mode']) || 'admin' !== $args['mode']) {
                throw new InvalidArgumentException('When mode is specified, mode must be "admin"');
            }

            return ['mode' => 'admin'];
        }

        throw new InvalidArgumentException('Args are invalid!');
    }

    public static function DaftRouterHttpRouteArgsTyped(array $args, string $method) : array
    {
        return static::DaftRouterHttpRouteArgs($args, $method);
    }

    public static function DaftRouterHttpRoute(array $args, string $method = 'GET') : string
    {
        $args = static::DaftRouterHttpRouteArgsTyped($args, $method);

        return ('admin' === ($args['mode'] ?? null)) ? '/admin/login' : '/login';
    }
}
