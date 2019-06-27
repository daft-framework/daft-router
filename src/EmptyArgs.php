<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftRouter;

use BadMethodCallException;

/**
* @psalm-type T = array<empty, empty>
*
* @template-implements TypedArgsInterface<T>
*/
final class EmptyArgs implements TypedArgsInterface
{
    use TypedArgsInterfaceImmutableSet;

    const COUNT_EMPTY = 0;

    public function __construct()
    {
    }

    public function __get(string $k)
    {
        /**
        * @var string
        */
        $k = $k;

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
