<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftRouter;

use Symfony\Component\HttpFoundation\Response;

/**
* @psalm-type T1 = array<empty, empty>
* @template R_EMPTY as Response
* @template HTTP_METHOD as 'GET'|'POST'|'CONNECT'|'DELETE'|'HEAD'|'OPTIONS'|'PATCH'|'PURGE'|'PUT'|'TRACE'
* @template HTTP_METHOD_DEFAULT as 'GET'|'POST'|'CONNECT'|'DELETE'|'HEAD'|'OPTIONS'|'PATCH'|'PURGE'|'PUT'|'TRACE'
*
* @template-implements DaftRouteAcceptsEmptyArgs<T1, T1, TypedArgs, R_EMPTY, Response, HTTP_METHOD, HTTP_METHOD_DEFAULT>
*/
abstract class DaftRouteAcceptsOnlyEmptyArgs implements DaftRouteAcceptsEmptyArgs
{
	/**
	* @template-use DaftRouterAutoMethodCheckingTrait<HTTP_METHOD>
	*/
	use DaftRouterAutoMethodCheckingTrait;

	/**
	* @psalm-suppress MoreSpecificImplementedParamType
	*
	* @param T1 $args
	* @param HTTP_METHOD|null $method If null, use DaftRoute::DaftRouterHttpRouteDefaultMethod()
	*
	* @return null
	*/
	final public static function DaftRouterHttpRouteArgsTyped(
		array $args,
		string $method = null
	) : ? TypedArgs {
		return null;
	}
}
