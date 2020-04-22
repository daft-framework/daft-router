<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftRouter;

use InvalidArgumentException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @psalm-type THTTP = 'GET'|'POST'|'CONNECT'|'DELETE'|'HEAD'|'OPTIONS'|'PATCH'|'PURGE'|'PUT'|'TRACE'
 */
interface DaftRouteAcceptsTypedArgs extends DaftRoute
{
	public static function DaftRouterHandleRequestWithTypedArgs(
		Request $request,
		TypedArgs $args
	) : Response;

	/**
	 * @param THTTP|null $method
	 *
	 * @throws InvalidArgumentException if no uri could be found
	 */
	public static function DaftRouterHttpRouteWithTypedArgs(
		TypedArgs $args,
		string $method = null
	) : string;
}
