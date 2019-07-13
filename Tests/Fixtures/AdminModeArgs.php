<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftRouter\Tests\Fixtures;

use SignpostMarv\DaftRouter\TypedArgs;

/**
* @template T as array{mode:'admin'}
*
* @template-extends StringModeArgs<T>
*/
class AdminModeArgs extends StringModeArgs
{
	/**
	* @param T $args
	*/
	public function __construct(array $args = ['mode' => 'admin'])
	{
		parent::__construct($args);
	}
}
