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
* @deprecated
*/
interface HttpRouteGenerator extends Countable, IteratorAggregate
{
    /**
    * Keys yielded from the generator should be route class names, values should be arguments
    * i.e. `yield DaftRoute::class => ['foo' => 'bar'];`.
    *
    * @psalm-return Generator<class-string<\SignpostMarv\DaftRouter\DaftRoute>, array<string, string>, mixed, void>
    */
    public function getIterator() : Generator;
}
