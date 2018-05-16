<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftRouter\Tests;

use PHPUnit\Framework\TestCase;

class Base extends TestCase
{
    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);

        $this->backupGlobals = false;
        $this->backupStaticAttributes = false;
        $this->runTestInSeparateProcess = false;
    }
}
