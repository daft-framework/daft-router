<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftRouter\Tests\FastRoute\RouteParser;

use FastRoute\RouteParser\StdTest as Base;

class StdTest extends Base
{
    public function __construct(string $name = '', array $data = [], string $dataName = '')
    {
        parent::__construct($name, $data, $dataName);

        $this->backupGlobals = false;
        $this->backupStaticAttributes = false;
        $this->runTestInSeparateProcess = false;
    }

    protected function setExpectedException(string $className, string $message)
    {
        static::expectException($className);
        static::expectExceptionMessage($message);
    }
}
