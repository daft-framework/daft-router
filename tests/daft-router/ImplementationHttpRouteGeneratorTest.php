<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftRouter\Tests;

use Generator;
use InvalidArgumentException;
use RuntimeException;
use SignpostMarv\DaftRouter\DaftRoute;
use SignpostMarv\DaftRouter\HttpRouteGenerator;
use Throwable;

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
    * @psalm-return array<int, array{0:class-string<HttpRouteGenerator\HttpRouteGenerator>, 1:mixed[], 2:class-string<Throwable>, 3:string}>
    */
    public function DataProviderSingleRouteGeneratorConstructorFailure() : array
    {
        return [
            [
                HttpRouteGenerator\SingleRouteGeneratorFromArray::class,
                [
                    InvalidArgumentException::class,
                    [],
                ],
                InvalidArgumentException::class,
                (
                    'Argument 1 passed to ' .
                    HttpRouteGenerator\SingleRouteGenerator::class .
                    '::__construct must be an implementation of ' .
                    DaftRoute::class .
                    ', ' .
                    InvalidArgumentException::class .
                    ' given!'
                ),
            ],
        ];
    }

    /**
    * @psalm-return array<int, array{0:class-string<HttpRouteGenerator\HttpRouteGenerator>, 1:mixed[], 2:class-string<Throwable>, 3:string}>
    */
    public function DataProviderSingleRouteGeneratorIteratorFailure() : array
    {
        return [
            [
                HttpRouteGenerator\SingleRouteGeneratorFromArray::class,
                [
                    Fixtures\AdminHome::class,
                    [1],
                ],
                InvalidArgumentException::class,
                (
                    'Argument 2 passed to ' .
                    HttpRouteGenerator\SingleRouteGeneratorFromArray::class .
                    '::__construct() had a non-array value at index 0'
                ),
            ],
        ];
    }

    /**
    * @return array<int, array<int, array<int|string, int>>>
    */
    public function DataProviderHttpRouteGeneratorToRoutesIteratorFailure() : array
    {
        return [
            [
                [1 => 2],
            ],
            [
                ['three' => 4],
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
    * @psalm-param class-string<DaftRoute> $route
    *
    * @param array<string, string> $args
    *
    * @dataProvider DataProviderForSingleRouteGeneratorGeneratorManual
    */
    public function testHttpRouteGeneratorManual(
        string $route,
        array $args,
        string $expected
    ) : void {
        if ( ! is_a($route, DaftRoute::class, true)) {
            throw new InvalidArgumentException(
                'Argument 1 passed to ' .
                __METHOD__ .
                ' must be an implementation of ' .
                DaftRoute::class .
                ', ' .
                $route .
                ' given!'
            );
        }

        $result = $route::DaftRouterHttpRoute($args);

        static::assertSame($expected, $result);
    }

    /**
    * @param array<string, array> $singleRouteGeneratorFromArrayArgs
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

            /**
            * @var array<int, array>
            */
            $arrayOfArgs = array_filter(
                array_filter(
                    $arrayOfArgs,
                    'is_array'
                ),
                'is_int',
                ARRAY_FILTER_USE_KEY
            );

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

    /**
    * @psalm-param class-string<HttpRouteGenerator\HttpRouteGenerator> $implementation
    * @psalm-param class-string<Throwable> $expectedException
    *
    * @dataProvider DataProviderSingleRouteGeneratorConstructorFailure
    */
    public function testSingleRouteGeneratorConstructorFailure(
        string $implementation,
        array $ctorArgs,
        string $expectedException,
        string $expectedExceptionMessage
    ) : void {
        if ( ! is_a($implementation, HttpRouteGenerator\SingleRouteGenerator::class, true)) {
            throw new InvalidArgumentException(
                'Argument 1 passed to ' .
                __METHOD__ .
                ' must be an implementation of ' .
                HttpRouteGenerator\SingleRouteGenerator::class .
                ', ' .
                $implementation .
                ' given!'
            );
        }

        static::expectException($expectedException);
        static::expectExceptionMessage($expectedExceptionMessage);

        new $implementation(...$ctorArgs);
    }

    /**
    * @psalm-param class-string<HttpRouteGenerator\HttpRouteGenerator> $implementation
    * @psalm-param class-string<Throwable> $expectedException
    *
    * @dataProvider DataProviderSingleRouteGeneratorIteratorFailure
    */
    public function testSingleRouteGeneratorIteratorFailure(
        string $implementation,
        array $ctorArgs,
        string $expectedException,
        string $expectedExceptionMessage
    ) : void {
        if ( ! is_a($implementation, HttpRouteGenerator\SingleRouteGenerator::class, true)) {
            throw new InvalidArgumentException(
                'Argument 1 passed to ' .
                __METHOD__ .
                ' must be an implementation of ' .
                HttpRouteGenerator\SingleRouteGenerator::class .
                ', ' .
                $implementation .
                ' given!'
            );
        }

        /**
        * @var iterable<int, string>
        */
        $obj = new $implementation(...$ctorArgs);

        static::expectException($expectedException);
        static::expectExceptionMessage($expectedExceptionMessage);

        foreach ($obj as $v) {
        }
    }

    /**
    * @dataProvider DataProviderHttpRouteGeneratorToRoutesIteratorFailure
    */
    public function testHttpRouteGeneratorToRoutesIteratorFailure(array $badsource) : void
    {
        $bad = new Fixtures\HttpRouteGenerator\BadHttpRouteGeneratorToRoutes(
            new HttpRouteGenerator\SingleRouteGeneratorFromArray(
                Fixtures\AdminHome::class,
                [
                    [],
                    [],
                ]
            )
        );
        $bad->ChangeToBadGenerator($badsource);

        static::expectException(RuntimeException::class);
        static::expectExceptionMessage(
            'Keys yielded from generator must be implementations of ' .
            DaftRoute::class
        );

        /**
        * @var iterable<int, string>
        */
        $bad = $bad;

        foreach ($bad as $v) {
        }
    }
}
