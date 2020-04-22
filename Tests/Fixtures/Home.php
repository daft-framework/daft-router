<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftRouter\Tests\Fixtures;

use InvalidArgumentException;
use SignpostMarv\DaftRouter\DaftRoute;
use SignpostMarv\DaftRouter\DaftRouteAcceptsOnlyEmptyArgs;
use SignpostMarv\DaftRouter\DaftRouterHttpRouteDefaultMethodGet;
use SignpostMarv\DaftRouter\TypedArgs;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class Home extends DaftRouteAcceptsOnlyEmptyArgs
{
	use DaftRouterHttpRouteDefaultMethodGet;

	public static function DaftRouterHandleRequestWithEmptyArgs(Request $request) : Response
	{
		return new Response('');
	}

	public static function DaftRouterRoutes() : array
	{
		return [
			'/' => ['GET'],
		];
	}

	/**
	 * @param 'GET'|null $method
	 */
	public static function DaftRouterHttpRouteWithEmptyArgs(string $method = null) : string
	{
		if ( ! is_null($method)) {
			static::DaftRouterAutoMethodChecking($method);
		}

		return '/';
	}
}
