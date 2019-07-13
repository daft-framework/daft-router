<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftRouter;

use InvalidArgumentException;

/**
* @template HTTP_METHOD as 'GET'|'POST'|'CONNECT'|'DELETE'|'HEAD'|'OPTIONS'|'PATCH'|'PURGE'|'PUT'|'TRACE'
*/
trait DaftRouterAutoMethodCheckingTrait
{
	/**
	* @return array<string, array<int, HTTP_METHOD>> an array of URIs & methods
	*/
	abstract public static function DaftRouterRoutes() : array;

	protected static function DaftRouterAutoMethodChecking(string $method) : void
	{
		$methods = array_merge([], ...array_values(static::DaftRouterRoutes()));

		if ( ! in_array($method, $methods, true)) {
			throw new InvalidArgumentException('Specified method not supported!');
		}
	}
}
