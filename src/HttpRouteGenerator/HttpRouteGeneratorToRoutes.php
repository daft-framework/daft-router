<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftRouter\HttpRouteGenerator;

use Countable;
use Generator;
use IteratorAggregate;

/**
* @template-implements IteratorAggregate<int, string>
*/
class HttpRouteGeneratorToRoutes implements Countable, IteratorAggregate
{
    /**
    * @var HttpRouteGenerator
    */
    protected $generator;

    public function __construct(HttpRouteGenerator $generator)
    {
        $this->generator = $generator;
    }

    public function count() : int
    {
        return $this->generator->count();
    }

    /**
    * @return Generator<int, string, mixed, void>
    */
    public function getIterator() : Generator
    {
        $generator = $this->generator;

        foreach ($generator as $route => $args) {
            yield $route::DaftRouterHttpRoute($route::DaftRouterHttpRouteArgsTyped($args, 'GET'));
        }
    }
}
