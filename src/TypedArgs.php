<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftRouter;

use DateTimeImmutable;
use SignpostMarv\DaftTypedObject\AbstractDaftTypedObject;

/**
* @template T as array<string, scalar|DateTimeImmutable|null>
* @template S as array<string, scalar|null>
*
* @template-extends AbstractDaftTypedObject<T, S>
*/
abstract class TypedArgs extends AbstractDaftTypedObject
{
	/**
	* @param T $args
	*/
	abstract public function __construct(array $args);
}
