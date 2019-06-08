<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftRouter\Tests\Fixtures;

use SignpostMarv\DaftRouter\TypedArgs;

/**
* @template T as array{mode:string}
*
* @template-extends TypedArgs<T>
*/
class StringModeArgs extends TypedArgs
{
}
