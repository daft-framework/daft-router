<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftRouter\Tests\Fixtures;

use Generator;
use SignpostMarv\DaftInterfaceCollector\StaticMethodCollector as Base;

class BadStaticMethodCollector extends Base
{
    public function Collect(string ...$implementations) : Generator
    {
        yield [1];
    }
}
