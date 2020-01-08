<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftRouter;

use DateTimeImmutable;
use Symfony\Component\HttpFoundation\Response;

abstract class DaftRouteAcceptsBothEmptyAndTypedArgs implements DaftRouteAcceptsEmptyArgs, DaftRouteAcceptsTypedArgs
{
	use DaftRouterAutoMethodCheckingTrait;
}
