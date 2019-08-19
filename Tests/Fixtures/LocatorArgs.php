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
* @psalm-type S = array{locator:string}
*
* @template-extends TypedArgs<T, S>
*/
class LocatorArgs extends TypedArgs
{
	const TYPED_PROPERTIES = [
		'locator',
	];

	/**
	* @readonly
	*
	* @var string
	*/
	public $locator;

	/**
	* @param T $args
	*/
	public function __construct(array $args)
	{
		$this->locator = $args['locator'];
	}
}
