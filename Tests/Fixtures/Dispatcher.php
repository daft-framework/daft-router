<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftRouter\Tests\Fixtures;

use InvalidArgumentException;
use SignpostMarv\DaftRouter\DaftRequestInterceptor;
use SignpostMarv\DaftRouter\DaftResponseModifier;
use SignpostMarv\DaftRouter\DaftRouteAcceptsEmptyArgs;
use SignpostMarv\DaftRouter\DaftRouteAcceptsTypedArgs;
use SignpostMarv\DaftRouter\Router\Dispatcher as Base;
use SignpostMarv\DaftRouter\TypedArgs;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class Dispatcher extends Base
{
	/**
	* @param class-string<DaftRouteAcceptsEmptyArgs>|class-string<DaftRouteAcceptsTypedArgs> $route
	* @param array<int, class-string<DaftRequestInterceptor>> $firstPass
	* @param array<int, class-string<DaftResponseModifier>> $secondPass
	*/
	public function handleRouteInfoResponseParentPublic(
		Request $request,
		string $route,
		? TypedArgs $routeArgs,
		array $firstPass,
		array $secondPass
	) : Response {
		return parent::handleRouteInfoResponse(
			$request,
			$route,
			$routeArgs,
			$firstPass,
			$secondPass
		);
	}

	/**
	* @param class-string<DaftRouteAcceptsEmptyArgs>|class-string<DaftRouteAcceptsTypedArgs> $route
	* @param null|TypedArgs $routeArgs
	* @param array<int, class-string<DaftRequestInterceptor>> $firstPass
	* @param array<int, class-string<DaftResponseModifier>> $secondPass
	*/
	protected function handleRouteInfoResponse(
		Request $request,
		string $route,
		? TypedArgs $routeArgs,
		array $firstPass,
		array $secondPass
	) : Response {
		$resp = $this->RunMiddlewareFirstPass($request, ...$firstPass);

		if ( ! ($resp instanceof Response)) {
			if ($routeArgs instanceof TypedArgs) {
				if ( ! is_a($route, DaftRouteAcceptsTypedArgs::class, true)) {
					throw new InvalidArgumentException(
						'Cannot handle typed request on route that does not implement ' .
						DaftRouteAcceptsTypedArgs::class
					);
				}

				$resp = $route::DaftRouterHandleRequestWithTypedArgs($request, $routeArgs);
			} else {
				if ( ! is_a($route, DaftRouteAcceptsEmptyArgs::class, true)) {
					throw new InvalidArgumentException(
						'Cannot handle typed request on route that does not implement ' .
						DaftRouteAcceptsEmptyArgs::class
					);
				}

				$resp = $route::DaftRouterHandleRequestWithEmptyArgs($request);
			}
		}

		$resp = $this->RunMiddlewareSecondPass($request, $resp, ...$secondPass);

		return $resp;
	}
}
