<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftRouter;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
* @psalm-type TIN = Response
* @psalm-type TOUT = Response
*
* @template T1 as Response
* @template T2 as Response
*/
interface DaftResponseModifier extends DaftRouteFilter
{
	/**
	* @param TIN $response
	*
	* @return TOUT
	*/
	public static function DaftRouterMiddlewareModifier(
		Request $request,
		Response $response
	) : Response;
}
