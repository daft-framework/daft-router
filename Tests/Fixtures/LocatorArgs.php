<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftRouter\Tests\Fixtures;

use InvalidArgumentException;
use SignpostMarv\DaftRouter\TypedArgs;

/**
* @psalm-type T = array{locator:string}
*
* @template-extends TypedArgs<T>
*
* @property-read string $locator
*/
class LocatorArgs extends TypedArgs
{
	/**
	* @var T
	*/
	protected $typed;

	/**
	* @param T $args
	*/
	public function __construct(array $args)
	{
		/**
		* @var T
		*/
		$this->typed = $args;
	}
}
