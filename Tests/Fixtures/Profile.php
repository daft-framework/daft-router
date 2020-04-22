<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftRouter\Tests\Fixtures;

use InvalidArgumentException;
use SignpostMarv\DaftRouter\DaftRoute;
use SignpostMarv\DaftRouter\DaftRouteAcceptsOnlyTypedArgs;
use SignpostMarv\DaftRouter\DaftRouterAutoMethodCheckingTrait;
use SignpostMarv\DaftRouter\DaftRouterHttpRouteDefaultMethodGet;
use SignpostMarv\DaftRouter\TypedArgs;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @psalm-type THTTP = 'GET'|'POST'|'CONNECT'|'DELETE'|'HEAD'|'OPTIONS'|'PATCH'|'PURGE'|'PUT'|'TRACE'
 * @psalm-type SLUG = array{id:int, slug:string}
 * @psalm-type SANS_SLUG = array{id:int}
 * @psalm-type S_SLUG = array{id:string, slug:string}
 * @psalm-type S_SANS_SLUG = array{id:string}
 * @psalm-type R = Response
 * @psalm-type HTTP_METHOD = 'GET'|'GET'
 */
class Profile extends DaftRouteAcceptsOnlyTypedArgs
{
	use DaftRouterHttpRouteDefaultMethodGet;

	const MIN_EXPECTED_ARGS = 1;

	const MAX_EXPECTED_ARGS = 2;

	/**
	 * @param IntIdArgs|IntIdStringSlugArgs $args
	 */
	public static function DaftRouterHandleRequestWithTypedArgs(
		Request $request,
		TypedArgs $args
	) : Response {
		/**
		 * @var THTTP
		 */
		$method = $request->getMethod();
		static::DaftRouterAutoMethodChecking($method);

		return new Response('');
	}

	public static function DaftRouterRoutes() : array
	{
		return [
			'/profile/{id:\d+}[~{slug:[^\/]+}]' => ['GET'],
		];
	}

	/**
	 * @param IntIdArgs|IntIdStringSlugArgs $args
	 * @param 'GET'|null $method
	 */
	public static function DaftRouterHttpRouteWithTypedArgs(
		TypedArgs $args,
		string $method = null
	) : string {
		$method = $method ?? static::DaftRouterHttpRouteDefaultMethod();
		static::DaftRouterAutoMethodChecking($method);

		if ($args instanceof IntIdStringSlugArgs) {
			return
				'/profile/' .
				rawurlencode((string) $args->id) .
				'~' .
				rawurlencode($args->slug);
		}

		return
			'/profile/' .
			rawurlencode((string) $args->id);
	}

	/**
	 * @param S_SLUG|S_SANS_SLUG $args
	 *
	 * @return IntIdArgs|IntIdStringSlugArgs
	 */
	public static function DaftRouterHttpRouteArgsTyped(
		array $args,
		string $method = null
	) : ? TypedArgs {
		if (isset($args['slug'])) {
			return IntIdStringSlugArgs::__fromArray($args);
		}

		return IntIdArgs::__fromArray($args);
	}
}
