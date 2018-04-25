<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftRouter\Tests\Fixtures;

class Login extends Home
{
    public static function DaftRouterRoutes() : array
    {
        return [
            '/login' => ['GET', 'POST'],
        ];
    }
}
