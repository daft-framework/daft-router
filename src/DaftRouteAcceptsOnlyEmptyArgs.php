<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftRouter;

use Symfony\Component\HttpFoundation\Response;

abstract class DaftRouteAcceptsOnlyEmptyArgs implements DaftRouteAcceptsEmptyArgs
{
	use DaftRouterAutoMethodCheckingTrait;

	final public static function DaftRouterHttpRouteArgsTyped(
		array $args,
		string $method = null
	) : ? TypedArgs {
		return null;
	}
}
