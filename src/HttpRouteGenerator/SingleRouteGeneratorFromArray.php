<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftRouter\HttpRouteGenerator;

use Generator;
use InvalidArgumentException;

class SingleRouteGeneratorFromArray extends SingleRouteGenerator
{
    /**
    * @var array<int|string, scalar|array|object|null>
    */
    protected $arrayOfArgs = [];

    /**
    * @param array<int, scalar|array|object|null> $arrayOfArgs
    */
    public function __construct(string $route, array $arrayOfArgs)
    {
        parent::__construct($route);

        $this->arrayOfArgs = $arrayOfArgs;
    }

    public function count() : int
    {
        return count($this->arrayOfArgs);
    }

    public function getIterator() : Generator
    {
        foreach ($this->arrayOfArgs as $i => $args) {
            if ( ! is_array($args)) {
                throw new InvalidArgumentException(
                    'Argument 2 passed to ' .
                    __CLASS__ .
                    '::__construct() had a non-array value at index ' .
                    $i
                );
            }

            yield $this->route => $args;
        }
    }
}
