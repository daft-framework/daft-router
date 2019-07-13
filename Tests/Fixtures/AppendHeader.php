<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftRouter\Tests\Fixtures;

use SignpostMarv\DaftRouter\DaftResponseModifier;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AppendHeader implements DaftResponseModifier
{
	public static function DaftRouterMiddlewareModifier(
		Request $request,
		Response $response
	) : Response {
		$response->headers->set('foo', 'bar');

		return $response;
	}

	/**
	* @return array<int, string> URI prefixes
	*/
	public static function DaftRouterRoutePrefixExceptions() : array
	{
		return [
			'/login',
		];
	}

	/**
	* @return array<int, string> URI prefixes
	*/
	public static function DaftRouterRoutePrefixRequirements() : array
	{
		return [
			'/',
		];
	}
}
