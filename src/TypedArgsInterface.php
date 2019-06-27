<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftRouter;

use BadMethodCallException;
use Countable;
use DateTimeImmutable;

/**
* @template T as array<string, scalar|DateTimeImmutable>|array<empty, empty>
*/
interface TypedArgsInterface extends Countable
{
    /**
    * @template K as key-of<T>
    *
    * @param K $k
    *
    * @return T[K]
    */
    public function __get(string $k);

    /**
    * @param value-of<T> $v
    *
    * @throws BadMethodCallException as instances are intended to be immutable
    */
    public function __set(string $k, $v) : void;

    /**
    * @return T
    */
    public function toArray() : array;
}
