<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftRouter;

use BadMethodCallException;
use Countable;

final class EmptyArgs implements Countable
{
    use TypedArgsInterfaceImmutableSet;

    const COUNT_EMPTY = 0;

    public function __construct()
    {
    }

    public function __get(string $k)
    {
        throw new BadMethodCallException(
            __METHOD__ .
            '() cannot be called on ' .
            static::class .
            ' with ' .
            $k .
            ', ' .
            static::class .
            ' has no arguments!'
        );
    }

    public function count() : int
    {
        return self::COUNT_EMPTY;
    }

    public function toArray() : array
    {
        return [];
    }
}
