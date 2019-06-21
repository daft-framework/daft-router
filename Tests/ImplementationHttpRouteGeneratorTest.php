<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftRouter\Tests;

use Generator;
use PHPUnit\Framework\TestCase as Base;
use SignpostMarv\DaftRouter\DaftRoute;
use SignpostMarv\DaftRouter\HttpRouteGenerator;

class ImplementationHttpRouteGeneratorTest extends Base
{
    /**
    * @psalm-return array<int, array{0:array<class-string<DaftRoute>, array<int, array<string, string>>>, 1:array<int, string>}>
    */
    public function DataProviderForSingleRouteGeneratorGenerator() : array
    {
        return [
            [
                [
                    Fixtures\AdminHome::class => [
                        [],
                        [],
                    ],
                    Fixtures\Home::class => [
                        [],
                        [],
                    ],
                    Fixtures\Login::class => [
                        [],
                        ['mode' => 'admin'],
                    ],
                    Fixtures\Profile::class => [
                        ['id' => '1'],
                        ['id' => '2'],
                        ['id' => '1', 'slug' => 'foo'],
                        ['id' => '1', 'slug' => 'bar'],
                        ['id' => '2', 'slug' => 'baz'],
                        ['id' => '3', 'slug' => 'bat'],
                    ],
                ],
                [
                    '/admin',
                    '/admin',
                    '/',
                    '/',
                    '/login',
                    '/admin/login',
                    '/profile/1',
                    '/profile/2',
                    '/profile/1~foo',
                    '/profile/1~bar',
                    '/profile/2~baz',
                    '/profile/3~bat',
                ],
            ],
        ];
    }

    /**
    * @psalm-return Generator<int, array{0:class-string<DaftRoute>, 1:array<string, string>, 2:string}, mixed, void>
    */
    final public function DataProviderForSingleRouteGeneratorGeneratorManual() : Generator
    {
        /**
        * @var array<int, scalar|array|object|null>
        */
        $providedArgs = $this->DataProviderForSingleRouteGeneratorGenerator();

        foreach ($providedArgs as $dataProviderArgs) {
            static::assertIsArray($dataProviderArgs);

            /**
            * @var array
            */
            $dataProviderArgs = $dataProviderArgs;

            static::assertCount(2, $dataProviderArgs);

            list($routeArgs, $expected) = $dataProviderArgs;

            static::assertIsArray($routeArgs);
            static::assertIsArray($expected);

            /**
            * @var array<int, string>
            */
            $expected = $expected;

            $count = 0;

            /**
            * @var array<int|string, scalar|array|object|null>
            */
            $routeArgs = $routeArgs;

            foreach ($routeArgs as $route => $arrayOfArgs) {
                static::assertIsString($route);
                static::assertIsArray($arrayOfArgs);

                /**
                * @var array
                */
                $arrayOfArgs = $arrayOfArgs;

                $count += count($arrayOfArgs);
            }

            static::assertCount($count, $expected);

            $i = 0;

            /**
            * @var array<string, array<int|string, scalar|array|object|null>>
            *
            * @psalm-var array<class-string<DaftRoute>, array<int, array<string, string>>>
            */
            $routeArgs = $routeArgs;

            foreach ($routeArgs as $route => $arrayOfArgs) {
                foreach ($arrayOfArgs as $args) {
                    yield [$route, $args, $expected[$i]];

                    ++$i;
                }
            }
        }
    }

    /**
    * @param class-string<DaftRoute> $route
    * @param array<string, string> $args
    *
    * @dataProvider DataProviderForSingleRouteGeneratorGeneratorManual
    */
    public function testHttpRouteGeneratorManual(
        string $route,
        array $args,
        string $expected
    ) : void {
        $result = $route::DaftRouterHttpRoute($route::DaftRouterHttpRouteArgsTyped($args, 'GET'));

        static::assertSame($expected, $result);
    }

    /**
    * @param array<string, array> $singleRouteGeneratorFromArrayArgs
    *
    * @psalm-param array<class-string<DaftRoute>, array<int, array<string, string>>> $singleRouteGeneratorFromArrayArgs
    *
    * @dataProvider DataProviderForSingleRouteGeneratorGenerator
    */
    public function testHttpRouteGeneratorAutomatic(
        array $singleRouteGeneratorFromArrayArgs,
        array $expectedResult
    ) : void {
        $singleRouteGenerators = [];

        foreach ($singleRouteGeneratorFromArrayArgs as $route => $arrayOfArgs) {
            $initialCount = count($arrayOfArgs, COUNT_RECURSIVE);

            $newCount = count($arrayOfArgs, COUNT_RECURSIVE);

            static::assertSame($initialCount, $newCount);

            $singleRouteGenerators[] = new HttpRouteGenerator\SingleRouteGeneratorFromArray(
                $route,
                $arrayOfArgs
            );
        }

        /**
        * @var iterable<int, string>
        */
        $routes = new HttpRouteGenerator\HttpRouteGeneratorToRoutes(
            new HttpRouteGenerator\SingleRouteGeneratorGenerator(
                ...$singleRouteGenerators
            )
        );

        $expectedCount = count($expectedResult);

        static::assertCount($expectedCount, $routes);

        foreach ($routes as $i => $compareTo) {
            static::assertSame($expectedResult[$i] ?? null, $compareTo);
        }
    }
}