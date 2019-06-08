<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftRouter\HttpRouteGenerator;

use Generator;

class SingleRouteGeneratorFromArray extends SingleRouteGenerator
{
    /**
    * @var array<int, array<string, scalar>>
    */
    protected $arrayOfArgs = [];

    /**
    * @param class-string<\SignpostMarv\DaftRouter\DaftRoute> $route
    * @param array<int, array<string, scalar>> $arrayOfArgs
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

    /**
    * @psalm-return Generator<class-string<\SignpostMarv\DaftRouter\DaftRoute>, array<string, scalar>, mixed, void>
    */
    public function getIterator() : Generator
    {
        foreach ($this->arrayOfArgs as $args) {
            yield $this->route => $args;
        }
    }
}
