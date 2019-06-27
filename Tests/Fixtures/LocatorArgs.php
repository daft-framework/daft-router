<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftRouter\Tests\Fixtures;

use InvalidArgumentException;
use SignpostMarv\DaftRouter\TypedArgs;

/**
* @template T as array{locator:string}
*
* @template-extends TypedArgs<T>
*
* @property-read string $locator
*/
class LocatorArgs extends TypedArgs
{
    /**
    * @template K as key-of<T>
    *
    * @param array<K, string> $args
    */
    public function __construct(array $args)
    {
        $this->typed = [
            'locator' => $args['locator'],
        ];
    }
}
