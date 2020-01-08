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

class AdminHome extends DaftRouteAcceptsOnlyEmptyArgs
{
	use DaftRouterHttpRouteDefaultMethodGet;

	public static function DaftRouterHandleRequestWithEmptyArgs(Request $request) : Response
	{
		return new Response('');
	}

	public static function DaftRouterRoutes() : array
	{
		return [
			'/admin' => ['GET'],
		];
	}

	/**
	* @param 'GET'|null $method
	*/
	public static function DaftRouterHttpRouteWithEmptyArgs(string $method = null) : string
	{
		return '/admin';
	}
}
