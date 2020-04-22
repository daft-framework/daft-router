<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftRouter\Tests\Fixtures;

use SignpostMarv\DaftRouter\DaftRouteFilter;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class DoesNothing implements DaftRouteFilter
{
	/**
	 * @return array<int, string> URI prefixes
	 */
	public static function DaftRouterRoutePrefixExceptions() : array
	{
		return [];
	}

	/**
	 * @return array<int, string> URI prefixes
	 */
	public static function DaftRouterRoutePrefixRequirements() : array
	{
		return [];
	}
}
