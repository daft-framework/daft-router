<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftRouter;

use DateTimeImmutable;
use Symfony\Component\HttpFoundation\Response;

abstract class DaftRouteAcceptsOnlyTypedArgs implements DaftRouteAcceptsTypedArgs
{
	use DaftRouterAutoMethodCheckingTrait;
}
