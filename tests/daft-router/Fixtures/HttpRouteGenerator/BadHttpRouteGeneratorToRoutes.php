<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftRouter\Tests\Fixtures\HttpRouteGenerator;

use Generator;
use SignpostMarv\DaftRouter\HttpRouteGenerator\HttpRouteGeneratorToRoutes;

class BadHttpRouteGeneratorToRoutes extends HttpRouteGeneratorToRoutes
{
    /**
    * @var array
    */
    protected $badsource = [];

    /**
    * @var Generator|null
    */
    protected $generator;

    public function BadGenerator() : Generator
    {
        yield from $this->badsource;
    }

    public function ChangeToBadGenerator(array $badsource) : void
    {
        $this->badsource = $badsource;
        $this->generator = $this->BadGenerator();
    }
}
