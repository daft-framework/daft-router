<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftRouter;

use BadMethodCallException;
use Countable;
use Generator;
use IteratorAggregate;

/**
* @template T as array<string, scalar>
*/
abstract class TypedArgs implements Countable
{
    /**
    * @var T
    */
    protected $typed = [];

    /**
    * @template K as key-of<T>
    *
    * @param array<K, string> $args
    */
    public function __construct(array $args)
    {
        /**
        * @var T
        */
        $args = $args;

        $this->typed = $args;
    }

    /**
    * @template K as key-of<T>
    *
    * @param K $k
    *
    * @return T[K]
    */
    public function __get(string $k)
    {
        return $this->typed[$k];
    }

    /**
    * @param scalar $v
    */
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

    public function count() : int
    {
        return count($this->typed);
    }

    /**
    * @return T
    */
    public function toArray() : array
    {
        return $this->typed;
    }
}
