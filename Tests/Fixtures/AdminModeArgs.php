<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftRouter\Tests\Fixtures;

use SignpostMarv\DaftRouter\TypedArgs;

/**
* @template T as array{mode:'admin'}
* @template S as array{mode:'admin'}
*
* @template-extends StringModeArgs<T, S>
*/
class AdminModeArgs extends StringModeArgs
{
}
