<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftRouter;

use DateTimeImmutable;
use Symfony\Component\HttpFoundation\Response;

/**
* This will be flagged as deprecated soon.
*
* @template T1 as array<string, scalar|DateTimeImmutable|null>
* @template T1_STRINGS as array<string, string|null>
* @template T2 as TypedArgs
* @template R_EMPTY as Response
* @template R_TYPED as Response
* @template HTTP_METHOD as 'GET'|'POST'|'CONNECT'|'DELETE'|'HEAD'|'OPTIONS'|'PATCH'|'PURGE'|'PUT'|'TRACE'
* @template HTTP_METHOD_DEFAULT as 'GET'|'POST'|'CONNECT'|'DELETE'|'HEAD'|'OPTIONS'|'PATCH'|'PURGE'|'PUT'|'TRACE'
*/
interface DaftRoute
{
	/**
	* @return array<string, array<int, HTTP_METHOD>> an array of URIs & methods
	*/
	public static function DaftRouterRoutes() : array;

	/**
	* @return HTTP_METHOD_DEFAULT
	*/
	public static function DaftRouterHttpRouteDefaultMethod();

	/**
	* @param T1_STRINGS|array<empty, empty> $args
	* @param HTTP_METHOD|null $method If null, use DaftRoute::DaftRouterHttpRouteDefaultMethod()
	*
	* @return T2|EmptyArgs
	*/
	public static function DaftRouterHttpRouteArgsTyped(array $args, string $method = null);
}
