<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftRouter;

use DateTimeImmutable;
use JsonSerializable;

/**
* @template T as array<string, scalar|DateTimeImmutable|null>
*/
abstract class TypedArgs implements JsonSerializable
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

    /**
    * @return T
    */
    public function toArray() : array
    {
        return $this->typed;
    }

    public function jsonSerialize() : array
    {
        $keys = array_keys($this->typed);

        return array_combine($keys, array_map(
            (static::class . '::FormatPropertyForJson'),
            $keys,
            $this->typed
        ));
    }

    /**
    * @template K as key-of<T>
    *
    * @param K $property
    * @param scalar|DateTimeImmutable|null $value
    *
    * @return scalar|null
    */
    public static function FormatPropertyForJson(string $property, $value)
    {
        if ($value instanceof DateTimeImmutable) {
            /**
            * @var string
            */
            return $value->format('c');
        }

        /**
        * @var scalar|null
        */
        return $value;
    }
}
