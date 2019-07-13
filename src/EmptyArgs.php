<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftRouter;

use BadMethodCallException;
use JsonSerializable;

final class EmptyArgs implements JsonSerializable
{
	use TypedArgsInterfaceImmutableSet;

	public function __construct()
	{
	}

	public function __get(string $k)
	{
		throw new BadMethodCallException(
			__METHOD__ .
			'() cannot be called on ' .
			static::class .
			' with ' .
			$k .
			', ' .
			static::class .
			' has no arguments!'
		);
	}

	public function toArray() : array
	{
		return [];
	}

	public function jsonSerialize() : array
	{
		return [];
	}
}
