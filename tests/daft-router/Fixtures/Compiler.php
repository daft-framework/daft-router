<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftRouter\Tests\Fixtures;

use SignpostMarv\DaftRouter\Router\Compiler as Base;
use SignpostMarv\DaftRouter\Router\Dispatcher;

class Compiler extends Base
{
    /**
    * @var \SignpostMarv\DaftInterfaceCollector\StaticMethodCollector|null
    */
    private $collector;

    public function NudgeCompilerWithSourcesBad(string ...$sources) : void
    {
        $this->collector = new BadStaticMethodCollector(
            Base::CollectorConfig,
            Base::CollectorInterfacesConfig
        );

        $this->NudgeCompilerWithSources(...$sources);
    }

    public static function ObtainCompiler() : self
    {
        return new self();
    }

    /**
    * @param mixed $out
    */
    public static function EnsureDispatcherIsCorrectlyTypedPublic($out) : Dispatcher
    {
        return static::EnsureDispatcherIsCorrectlyTyped($out);
    }
}
