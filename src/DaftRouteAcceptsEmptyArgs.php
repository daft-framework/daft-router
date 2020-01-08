<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftRouter;

use DateTimeImmutable;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
* @psalm-type THTTP = 'GET'|'POST'|'CONNECT'|'DELETE'|'HEAD'|'OPTIONS'|'PATCH'|'PURGE'|'PUT'|'TRACE'
*/
interface DaftRouteAcceptsEmptyArgs extends DaftRoute
{
	public static function DaftRouterHandleRequestWithEmptyArgs(Request $request) : Response;

	/**
	* @param THTTP|null $method If null, use DaftRoute::DaftRouterHttpRouteDefaultMethod()
	*
	* @throws \InvalidArgumentException if no uri could be found
	*/
	public static function DaftRouterHttpRouteWithEmptyArgs(string $method = null) : string;
}
