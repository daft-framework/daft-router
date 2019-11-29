<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftRouter\Tests\Fixtures;

use SignpostMarv\DaftRouter\TypedArgs;

/**
* @template T as array{mode:string}
* @template S as array{mode:string}
*
* @template-extends TypedArgs<T, S>
*/
class StringModeArgs extends TypedArgs
{
	const TYPED_PROPERTIES = [
		'mode',
	];

	/**
	* @readonly
	*/
	public string $mode;

	/**
	* @param T $data
	*/
	public function __construct(array $data)
	{
		$this->mode = $data['mode'];
	}
}
