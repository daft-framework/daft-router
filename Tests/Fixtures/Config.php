<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftRouter\Tests\Fixtures;

use SignpostMarv\DaftRouter\DaftSource;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class Config implements DaftSource
{
	public static function DaftRouterRouteAndMiddlewareSources() : array
	{
		return [
			Home::class,
			Login::class,
			NotLoggedIn::class,
			AppendHeader::class,
			DoesNothing::class,
		];
	}
}
