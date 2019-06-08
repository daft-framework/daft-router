<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftRouter\Tests\Fixtures;

use SignpostMarv\DaftRouter\TypedArgs;

/**
* @template T as array{id:int, slug:string}
*
* @template-extends IntIdArgs<T>
*
* @property-read int $id
* @property-read string $slug
*/
class IntIdStringSlugArgs extends IntIdArgs
{
}
