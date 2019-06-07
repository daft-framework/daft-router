<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftRouter\HttpRouteGenerator;

use Countable;
use Generator;
use IteratorAggregate;
use RuntimeException;
use SignpostMarv\DaftRouter\DaftRoute;

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

    public function getIterator() : Generator
    {
        $generator = $this->generator;

        foreach ($generator as $route => $args) {
            yield $route::DaftRouterHttpRoute($args);
        }
    }
}
