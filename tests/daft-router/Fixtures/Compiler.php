<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftRouter\Tests\Fixtures;

use FastRoute\Dispatcher;
use SignpostMarv\DaftRouter\Router\Compiler as Base;
use function FastRoute\simpleDispatcher;

class Compiler extends Base
{
    public static function ObtainCompiler() : self
    {
        return new self();
    }
}
