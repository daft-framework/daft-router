<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftRouter\HttpRouteGenerator;

use Generator;

/**
* @deprecated
*/
class SingleRouteGeneratorGenerator implements HttpRouteGenerator
{
    /**
    * @var array<int, SingleRouteGenerator>
    */
    protected $generators = [];

    public function __construct(SingleRouteGenerator ...$generators)
    {
        $this->generators = $generators;
    }

    public function count() : int
    {
        $out = 0;

        foreach ($this->generators as $generator) {
            $out += $generator->count();
        }

        return $out;
    }

    /**
    * @return Generator<class-string<\SignpostMarv\DaftRouter\DaftRoute>, array<string, string>, mixed, void>
    */
    public function getIterator() : Generator
    {
        foreach ($this->generators as $generator) {
            /**
            * @var \IteratorAggregate<class-string<\SignpostMarv\DaftRouter\DaftRoute>, array<string, string>>
            */
            $generator = $generator;

            yield from $generator;
        }
    }
}
