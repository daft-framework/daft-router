<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftRouter;

use SignpostMarv\DaftTypedObject\DaftTypedObject;

/**
* @template T as array<empty, empty>
* @template S as array<empty, empty>
*
* @template-implements DaftTypedObject<T, S>
*/
final class EmptyArgs implements DaftTypedObject
{
	public function __construct(array $data = [])
	{
	}

	/**
	* @return S
	*/
	public function __toArray() : array
	{
		return [];
	}

	/**
	* @param S $array
	*/
	public static function __fromArray(array $array = []) : DaftTypedObject
	{
		return new EmptyArgs([]);
	}

	/**
	* @return S
	*/
	public function jsonSerialize() : array
	{
		return [];
	}

	/**
	* @param null $value
	*/
	public static function PropertyValueToScalarOrNull(
		string $_property,
		$value
	) {
	}

	/**
	* @param null $value
	*/
	public static function PropertyScalarOrNullToValue(
		string $_property,
		$value
	) {
	}
}
