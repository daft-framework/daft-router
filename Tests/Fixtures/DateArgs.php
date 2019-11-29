<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftRouter\Tests\Fixtures;

use DateTimeImmutable;
use SignpostMarv\DaftRouter\TypedArgs;

/**
* @template T as array{a:DateTimeImmutable, b:DateTimeImmutable}
* @template S as array{a:string, b:string}
*
* @template-extends TypedArgs<T, S>
*/
class DateArgs extends TypedArgs
{
	const TYPED_PROPERTIES = [
		'a',
		'b',
	];

	/**
	* @readonly
	*/
	public DateTimeImmutable $a;

	/**
	* @readonly
	*/
	public DateTimeImmutable $b;

	/**
	* @param T $data
	*/
	public function __construct(array $data)
	{
		$this->a = $data['a'];
		$this->b = $data['b'];
	}

	/**
	* @template K as key-of<T>
	*
	* @param K $property
	* @param T[K] $value
	*
	* @return S[K]
	*/
	public static function PropertyValueToScalarOrNull(
		string $property,
		$value
	) {
		/**
		* @var string
		*/
		$property = $property;

		if ('a' === $property) {
			/**
			* @var S[K]
			*/
			return $value->format('Y-m-d');
		}

		/**
		* @var S[K]
		*/
		return $value->format('Y\WW');
	}

	/**
	* @template K as key-of<T>
	*
	* @param K $_property
	* @param S[K] $value
	*
	* @return T[K]
	*/
	public static function PropertyScalarOrNullToValue(
		string $_property,
		$value
	) {
		/**
		* @var string
		*/
		$value = $value;

		/**
		* @var T[K]
		*/
		return new DateTimeImmutable($value);
	}
}
