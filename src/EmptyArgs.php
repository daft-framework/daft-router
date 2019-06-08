<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftRouter;

use BadMethodCallException;

/**
* @template T as array<empty, empty>
*
* @template-extends TypedArgs<T>
*/
final class EmptyArgs extends TypedArgs
{
    const COUNT_EMPTY = 0;

    /**
    * @param T $args
    */
    public function __construct(array $args = [])
    {
    }

    public function __get(string $k)
    {
        throw new BadMethodCallException(
            __METHOD__ .
            '() cannot be called on ' .
            static::class .
            ' with ' .
            (string) $k .
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
