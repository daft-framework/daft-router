<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftRouter\Tests\Fixtures;

use SignpostMarv\DaftRouter\TypedArgs;

/**
* @template T as array{id:int, slug:string}
* @template S as array{id:string, slug:string}
*
* @template-extends IntIdArgs<T, S>
*/
class IntIdStringSlugArgs extends IntIdArgs
{
	const TYPED_PROPERTIES = [
		'id',
		'slug',
	];

	/**
	* @readonly
	*
	* @var string
	*/
	public $slug;

	/**
	* @param T $args
	*/
	public function __construct(array $args)
	{
		parent::__construct($args);

		$this->slug = $args['slug'];
	}
}
