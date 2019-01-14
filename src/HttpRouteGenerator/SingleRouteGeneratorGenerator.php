<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftRouter\HttpRouteGenerator;

use Generator;

class SingleRouteGeneratorGenerator implements HttpRouteGenerator
{
    /**
    * @var array<int, HttpRouteGenerator>
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

    public function getIterator() : Generator
    {
        foreach ($this->generators as $generator) {
            /**
            * @var iterable<string, array>
            */
            $generator = $generator;

            foreach ($generator as $route => $args) {
                yield $route => $args;
            }
        }
    }
}
