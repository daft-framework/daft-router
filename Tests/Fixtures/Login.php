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
use SignpostMarv\DaftRouter\TypedArgs;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
* @psalm-type THTTP = 'GET'|'POST'|'CONNECT'|'DELETE'|'HEAD'|'OPTIONS'|'PATCH'|'PURGE'|'PUT'|'TRACE'
* @psalm-type T1 = array{mode:'admin'}
* @psalm-type T2 = AdminModeArgs
* @psalm-type T3 = Response
* @psalm-type HTTP_METHOD = 'GET'|'POST'
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
	* @return T2|null
	*/
	public static function DaftRouterHttpRouteArgsTyped(
		array $args,
		string $method = null
	) : ? TypedArgs {
		if ('admin' === ($args['mode'] ?? null)) {
			return AdminModeArgs::__fromArray([
				'mode' => 'admin',
			]);
		}

		return null;
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
	* @param THTTP|null $method
	*/
	public static function DaftRouterHttpRouteWithTypedArgs(
		TypedArgs $args,
		string $method = null
	) : string {
		if ( ! is_null($method)) {
			static::DaftRouterAutoMethodChecking($method);
		}

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
