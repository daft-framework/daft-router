<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftRouter;

use BadMethodCallException;
use Countable;

/**
* @template T as array<string, scalar>
*
* @template-implements TypedArgsInterface<T>
*/
abstract class TypedArgs implements TypedArgsInterface
{
    use TypedArgsInterfaceImmutableSet;

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
