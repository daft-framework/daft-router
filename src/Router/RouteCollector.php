<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftRouter\Router;

use FastRoute\RouteCollector as Base;
use SignpostMarv\DaftRouter\DaftRequestInterceptor;
use SignpostMarv\DaftRouter\DaftResponseModifier;
use SignpostMarv\DaftRouter\DaftRoute;

final class RouteCollector extends Base
{
	/**
	 * @param string|string[] $httpMethod
	 * @param string $route
	 * @param mixed $handler
	 * @param array{
	 *	SignpostMarv\DaftRouter\DaftRequestInterceptor:array<
	 *		int,
	 *		class-string<DaftRequestInterceptor>
	 *	>,
	 *	SignpostMarv\DaftRouter\DaftResponseModifier:array<
	 *		int,
	 *		class-string<DaftResponseModifier>
	 *	>,
	 *	0:class-string<DaftRoute>
	 * } $handler
	 */
	public function addRoute($httpMethod, $route, $handler) : void
	{
		foreach ((array) $httpMethod as $method) {
			parent::addRoute($method, $route, $handler);
		}
	}
}
