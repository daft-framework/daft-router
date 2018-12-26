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
        /**
        * @var iterable<int|string, array<string, string>>
        */
        $generator = $this->generator;

        foreach ($generator as $route => $args) {
            if ( ! is_string($route) || ! is_a($route, DaftRoute::class, true)) {
                throw new RuntimeException(
                    'Keys yielded from generator must be implementations of ' .
                    DaftRoute::class
                );
            }

            yield $route::DaftRouterHttpRoute($args);
        }
    }
}
