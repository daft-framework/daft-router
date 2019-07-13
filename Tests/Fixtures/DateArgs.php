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
*
* @template-extends TypedArgs<T>
*
* @property-read DateTimeImmutable $a
* @property-read DateTimeImmutable $b
*/
class DateArgs extends TypedArgs
{
	/**
	* @var T
	*/
	protected $typed;

	/**
	* @param array{a:string, b:string} $data
	*/
	public function __construct(array $data)
	{
		/**
		* @var T
		*/
		$typed = [
			'a' => new DateTimeImmutable($data['a']),
			'b' => new DateTimeImmutable($data['b']),
		];

		$this->typed = $typed;
	}

	/**
	* @param 'a'|'b' $property
	* @param DateTimeImmutable $value
	*/
	public static function FormatPropertyForJson(string $property, $value)
	{
		if ('a' === $property) {
			return $value->format('Y-m-d');
		}

		return $value->format('Y\WW');
	}
}
