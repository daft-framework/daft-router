<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftRouter;

use BadMethodCallException;
use Countable;

trait TypedArgsInterfaceImmutableSet
{
    final public function __set(string $k, $v) : void
    {
        throw new BadMethodCallException(
            static::class .
            '::$' .
            $k .
            ' is not writeable, cannot be set to ' .
            var_export($v, true)
        );
    }
}
