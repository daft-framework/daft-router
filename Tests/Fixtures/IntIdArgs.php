<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftRouter\Tests\Fixtures;

use InvalidArgumentException;
use SignpostMarv\DaftRouter\TypedArgs;

/**
* @template T as array{id:int}
*
* @template-extends TypedArgs<T>
*
* @property-read int $id
*/
class IntIdArgs extends TypedArgs
{
	/**
	* @param array{id:string} $args
	*/
	public function __construct(array $args)
	{
		if ( ! ctype_digit($args['id'])) {
			throw new InvalidArgumentException(
				'Argument 1 passed to ' .
				__METHOD__ .
				' must be a string-as-int, ' .
				var_export($args['id'], true) .
				' given!'
			);
		}

		$args['id'] = (int) $args['id'];

		/**
		* @var T
		*/
		$args = $args;

		$this->typed = $args;
	}
}
