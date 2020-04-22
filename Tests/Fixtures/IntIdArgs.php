<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftRouter\Tests\Fixtures;

use InvalidArgumentException;
use SignpostMarv\DaftRouter\TypedArgs;

/**
 * @template T as array{id:int}
 * @template S as array{id:string}
 *
 * @template-extends TypedArgs<T, S>
 */
class IntIdArgs extends TypedArgs
{
	const TYPED_PROPERTIES = [
		'id',
	];

	/**
	 * @readonly
	 */
	public int $id;

	/**
	 * @param T $args
	 */
	public function __construct(array $args)
	{
		$this->id = $args['id'];
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

		if ('id' === $property) {
			/**
			 * @var int
			 */
			$value = $value;

			/**
			 * @var S[K]
			 */
			return (string) $value;
		}

		/**
		 * @var S[K]
		 */
		return parent::PropertyValueToScalarOrNull($property, $value);
	}

	/**
	 * @template K as key-of<T>
	 *
	 * @param K $property
	 * @param S[K] $value
	 *
	 * @return T[K]
	 */
	public static function PropertyScalarOrNullToValue(
		string $property,
		$value
	) {
		/**
		 * @var string
		 */
		$property = $property;

		if ('id' === $property) {
			/**
			 * @var string
			 */
			$value = $value;

			if ( ! ctype_digit($value)) {
				throw new InvalidArgumentException(
					'Argument 1 passed to ' .
					__METHOD__ .
					' must be a string-as-int, ' .
					var_export($value, true) .
					' given!'
				);
			}

			/**
			 * @var T[K]
			 */
			return (int) $value;
		}

		/**
		 * @var T[K]
		 */
		return parent::PropertyScalarOrNullToValue($property, $value);
	}
}
