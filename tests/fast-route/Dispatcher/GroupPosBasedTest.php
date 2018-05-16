<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftRouter\Tests\FastRoute\Dispatcher;

use FastRoute\Dispatcher\GroupPosBasedTest as Base;

class GroupPosBasedTest extends Base
{
    public function __construct(string $name = '', array $data = [], string $dataName = '')
    {
        parent::__construct($name, $data, $dataName);

        $this->backupGlobals = false;
        $this->backupStaticAttributes = false;
        $this->runTestInSeparateProcess = false;
    }
}
