<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftRouter\Tests\Fixtures;

use function FastRoute\cachedDispatcher;
use SignpostMarv\DaftInterfaceCollector\StaticMethodCollector as BaseStaticMethodCollector;
use SignpostMarv\DaftRouter\DaftRoute;
use SignpostMarv\DaftRouter\DaftRouteFilter;
use SignpostMarv\DaftRouter\DaftSource;
use SignpostMarv\DaftRouter\Router\Compiler as Base;
use SignpostMarv\DaftRouter\Router\Dispatcher as BaseDispatcher;
use SignpostMarv\DaftRouter\Router\RouteCollector;

class Compiler extends Base
{
	protected BaseStaticMethodCollector $collector;

	protected function __construct()
	{
		parent::__construct();
		$this->collector = new BaseStaticMethodCollector(
			Base::CollectorConfig,
			Base::CollectorInterfacesConfig
		);
	}

	public static function ObtainCompiler() : self
	{
		return new static();
	}

	/**
	* @param mixed $out
	*/
	public static function EnsureDispatcherIsCorrectlyTypedPublic($out) : BaseDispatcher
	{
		return static::EnsureDispatcherIsCorrectlyTyped($out);
	}

	protected function ObtainCollector() : BaseStaticMethodCollector
	{
		return $this->collector;
	}
}
