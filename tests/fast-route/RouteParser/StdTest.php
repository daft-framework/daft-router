<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftRouter\Tests\FastRoute\RouteParser;

use FastRoute\RouteParser\StdTest as Base;
use Throwable;

class StdTest extends Base
{
    public function __construct(string $name = '', array $data = [], string $dataName = '')
    {
        parent::__construct($name, $data, $dataName);

        $this->backupGlobals = false;
        $this->backupStaticAttributes = false;
        $this->runTestInSeparateProcess = false;
    }

    /**
    * @psalm-param class-string<Throwable> $className
    */
    protected function setExpectedException(string $className, string $message) : void
    {
        static::expectException($className);
        static::expectExceptionMessage($message);
    }
}
