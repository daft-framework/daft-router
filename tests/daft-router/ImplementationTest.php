<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftRouter\Tests;

use FastRoute\Dispatcher;
use FastRoute\RouteParser\Std;
use Generator;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase as Base;
use SignpostMarv\DaftRouter\{
    DaftMiddleware,
    DaftRoute,
    DaftSource,
    Router\Compiler as BaseCompiler
};

class ImplementationTest extends Base
{
    public function DataProviderGoodSources() : Generator
    {
        yield from [
            [
                Fixtures\Config::class,
            ],
        ];
    }

    public function DataProviderMiddlewareWithExceptions() : Generator
    {
        yield from [
            [
                Fixtures\NotLoggedIn::class,
                Fixtures\Home::class,
                'GET',
                '/',
                Fixtures\Login::class,
                'GET',
                '/login',
            ],
        ];
    }

    protected static function YieldRoutesFromSource(string $source) : Generator
    {
        if (is_a($source, DaftRoute::class, true)) {
            yield $source;
        }
        if (is_a($source, DaftSource::class, true)) {
            foreach ($source::DaftRouterRouteAndMiddlewareSources() as $otherSource) {
                yield from static::YieldRoutesFromSource($otherSource);
            }
        }
    }

    protected static function YieldMiddlewareFromSource(string $source) : Generator
    {
        if (is_a($source, DaftMiddleware::class, true)) {
            yield $source;
        }
        if (is_a($source, DaftSource::class, true)) {
            foreach ($source::DaftRouterRouteAndMiddlewareSources() as $otherSource) {
                yield from static::YieldMiddlewareFromSource($otherSource);
            }
        }
    }

    public function DataProviderRoutes() : Generator
    {
        foreach ($this->DataProviderGoodSources() as $source) {
            yield from static::YieldRoutesFromSource(...$source);
        }
    }

    public function DataProviderRoutesWithNoArgs() : Generator
    {
        $parser = new Std;
        foreach ($this->DataProviderRoutes() as $route) {
            foreach ($route::DaftRouterRoutes() as $method => $uris) {
                $hasNoArgs = true;
                foreach ($uris as $uri) {
                    if (count($parser->parse($uri)) > 1) {
                        $hasNoArgs = false;
                        break;
                    }
                }

                if ($hasNoArgs) {
                    yield [$route, $method];
                }
            }
        }
    }

    /**
    * @dataProvider DataProviderGoodSources
    */
    public function testSources(string $className, ...$args) : void
    {
        $this->assertTrue(
            is_a($className, DaftSource::class, true),
            sprintf(
                'Source must be an implementation of %s, "%s" given.',
                DaftSource::class,
                $className
            )
        );

        $sources = $className::DaftRouterRouteAndMiddlewareSources();

        if (empty($sources)) {
            $this->markTestSkipped('No sources to test!');
        } else {
            $prevKey = key($sources);

            foreach (array_keys($sources) as $i => $k) {
                $this->assertInternalType('int', $k, 'Sources must be listed with integer keys!');
                if ($i > 0) {
                    $this->assertGreaterThan(
                        $prevKey,
                        $k,
                        'Sources must be listed with incremental keys!'
                    );
                    $this->assertSame(
                        $prevKey + 1,
                        $k,
                        'Sources must be listed with sequential keys!'
                    );
                }

                $source = $sources[$k];
                $this->assertInternalType('string', $source);
                $this->assertTrue(
                    (
                        is_a($source, DaftSource::class, true) ||
                        is_a($source, DaftRoute::class, true) ||
                        is_a($source, DaftMiddleware::class, true)
                    ),
                    'Sources must only be listed as routes, middleware or sources!'
                );

                $prevKey = $k;
            }
        }
    }

    /**
    * @depends testSources
    *
    * @dataProvider DataProviderRoutesWithNoArgs
    */
    public function testRoutesWithNoArgs(string $className, string $method) : void
    {
        $this->expectException(InvalidArgumentException::class);
        $className::DaftRouterHttpRoute(['foo' => 'bar'], $method);
    }

    public function testCompilerVerifyAddRouteThrowsException() : void
    {
        $compiler = Fixtures\Compiler::ObtainCompiler();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(sprintf(
            'Argument 1 passed to %s::%s must be an implementation of %s',
            BaseCompiler::class,
            'AddRoute',
            DaftRoute::class
        ));

        $compiler->AddRoute('stdClass');
    }

    /**
    * @depends testCompilerVerifyAddRouteThrowsException
    *
    * @dataProvider DataProviderGoodSources
    */
    public function testCompilerVerifyAddRouteAddsRoutes(string $className, ...$args) : void
    {
        $routes = [];
        $compiler = Fixtures\Compiler::ObtainCompiler();

        foreach (static::YieldRoutesFromSource($className) as $route) {
            $routes[] = $route;
            $compiler->AddRoute($route);
        }

        $this->assertSame($routes, $compiler->ObtainRoutes());
    }

    public function testCompilerVerifyAddMiddlewareThrowsException() : void
    {
        $compiler = Fixtures\Compiler::ObtainCompiler();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(sprintf(
            'Argument 1 passed to %s::%s must be an implementation of %s',
            BaseCompiler::class,
            'AddMiddleware',
            DaftMiddleware::class
        ));

        $compiler->AddMiddleware('stdClass');
    }

    /**
    * @depends testCompilerVerifyAddMiddlewareThrowsException
    *
    * @dataProvider DataProviderGoodSources
    */
    public function testCompilerVerifyAddMiddlewareAddsMiddlewares(string $className) : void
    {
        $middlewares = [];
        $compiler = Fixtures\Compiler::ObtainCompiler();

        foreach (static::YieldMiddlewareFromSource($className) as $middleware) {
            $middlewares[] = $middleware;
            $compiler->AddMiddleware($middleware);
        }

        $this->assertSame($middlewares, $compiler->ObtainMiddleware());
    }

    /**
    * @depends testCompilerVerifyAddRouteAddsRoutes
    * @depends testCompilerVerifyAddMiddlewareAddsMiddlewares
    *
    * @dataProvider DataProviderGoodSources
    */
    public function testCompilerDoesNotDuplicateConfigEntries(string $className) : void
    {
        $compiler = Fixtures\Compiler::ObtainCompiler();
        $routes = [];
        $middlewares = [];

        foreach (static::YieldRoutesFromSource($className) as $route) {
            $routes[] = $route;
        }
        foreach (static::YieldMiddlewareFromSource($className) as $middleware) {
            $middlewares[] = $middleware;
        }

        $compiler->NudgeCompilerWithSources($className);
        $this->assertSame($routes, $compiler->ObtainRoutes());
        $this->assertSame($middlewares, $compiler->ObtainMiddleware());

        $compiler->NudgeCompilerWithSources($className);
        $this->assertSame(
            $routes,
            $compiler->ObtainRoutes(),
            'Routes must be identical after adding a source more than once!'
        );
        $this->assertSame(
            $middlewares,
            $compiler->ObtainMiddleware(),
            'Middleware must be identical after adding a source more than once!'
        );
    }

    /**
    * @depends testCompilerVerifyAddRouteAddsRoutes
    * @depends testCompilerVerifyAddMiddlewareAddsMiddlewares
    *
    * @dataProvider DataProviderMiddlewareWithExceptions
    */
    public function testCompilerExcludesMiddleware(
        string $middleware,
        string $presentWith,
        string $presentWithMethod,
        string $presentWithUri,
        string $notPresentWith,
        string $notPresentWithMethod,
        string $notPresentWithUri
    ) : void {
        $this->assertTrue(is_a($middleware, DaftMiddleware::class, true));
        $this->assertTrue(is_a($presentWith, DaftRoute::class, true));
        $this->assertTrue(is_a($notPresentWith, DaftRoute::class, true));

        $dispatcher = Fixtures\Compiler::ObtainCompiler()->ObtainSimpleDispatcher(
            [],
            $middleware,
            $presentWith,
            $notPresentWith
        );

        $present = $dispatcher->dispatch($presentWithMethod, $presentWithUri);
        $notPresent = $dispatcher->dispatch(
            $notPresentWithMethod,
            $notPresentWithUri
        );

        $this->assertTrue($present[0] === Dispatcher::FOUND);
        $this->assertTrue($notPresent[0] === Dispatcher::FOUND);

        $this->assertSame(
            [
                $middleware,
                $presentWith,
            ],
            $present[1]
        );
        $this->assertSame(
            [
                $notPresentWith,
            ],
            $notPresent[1]
        );
    }
}
