<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftRouter\Tests\FastRoute\RouteParser;

use FastRoute\RouteParser\StdTest as Base;

class StdTest extends Base
{
    protected function setExpectedException(string $className, string $message) : void
    {
        $this->expectException($className);
        $this->expectExceptionMessage($message);
    }
}
