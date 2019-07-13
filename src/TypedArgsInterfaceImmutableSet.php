<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftRouter;

use BadMethodCallException;

trait TypedArgsInterfaceImmutableSet
{
	/**
	* @param scalar|array|object|null $v
	*/
	final public function __set(string $k, $v) : void
	{
		throw new BadMethodCallException(sprintf(
			'%s::$%s is not writeable, cannot be set to %s',
			static::class,
			$k,
			var_export($v, true)
		));
	}
}
