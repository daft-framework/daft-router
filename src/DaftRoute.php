<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftRouter;

/**
 * This should be flagged as deprecated soon.
 *
 * @psalm-type THTTP = 'GET'|'POST'|'CONNECT'|'DELETE'|'HEAD'|'OPTIONS'|'PATCH'|'PURGE'|'PUT'|'TRACE'
 */
interface DaftRoute
{
	/**
	 * @return array<string, array<int, THTTP>> an array of URIs & methods
	 */
	public static function DaftRouterRoutes() : array;

	/**
	 * @return THTTP
	 */
	public static function DaftRouterHttpRouteDefaultMethod();

	/**
	 * @param array<string, string|null>|array<empty, empty> $args
	 * @param THTTP|null $method If null, use DaftRoute::DaftRouterHttpRouteDefaultMethod()
	 */
	public static function DaftRouterHttpRouteArgsTyped(
		array $args,
		string $method = null
	) : ? TypedArgs;
}
