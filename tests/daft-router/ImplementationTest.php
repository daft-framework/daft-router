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
use RuntimeException;
use SignpostMarv\DaftRouter\DaftMiddleware;
use SignpostMarv\DaftRouter\DaftRoute;
use SignpostMarv\DaftRouter\DaftSource;
use SignpostMarv\DaftRouter\Router\Compiler as BaseCompiler;
use Symfony\Component\HttpFoundation\Request;

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

    public function DataProviderRoutes() : Generator
    {
        foreach ($this->DataProviderGoodSources() as $i => $args) {
            if ( ! is_array($args)) {
                throw new RuntimeException(sprintf(
                    'Non-array result yielded from %s::DataProviderGoodSources() at index %s',
                    static::class,
                    $i
                ));
            } elseif (count($args) < 1) {
                throw new RuntimeException(sprintf(
                    'Empty result yielded from %s::DataProviderGoodSources() at index %s',
                    static::class,
                    $i
                ));
            }

            $source = array_shift($args);

            if ( ! is_string($source)) {
                throw new RuntimeException(sprintf(
                    'Non-string result yielded from %s::DataProviderGoodSources() at index %s',
                    static::class,
                    $i
                ));
            }

            yield from static::YieldRoutesFromSource($source);
        }
    }

    public function DataProviderRoutesWithNoArgs() : Generator
    {
        $parser = new Std();
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

    protected function DataProviderHandler() : Generator
    {
        yield from [
            [
                [
                    Fixtures\Config::class,
                ],
                '',
                200,
                '',
                'https://example.com/?loggedin',
            ],
            [
                [
                    Fixtures\Config::class,
                ],
                '/',
                200,
                '',
                'https://example.com/?loggedin',
            ],
            [
                [
                    Fixtures\Config::class,
                ],
                '/foo/',
                200,
                '',
                'https://example.com/foo/?loggedin',
            ],
            [
                [
                    Fixtures\Config::class,
                ],
                '',
                302,
                (
                    '<!DOCTYPE html>' . "\n" .
                    '<html>' . "\n" .
                    '    <head>' . "\n" .
                    '        <meta charset="UTF-8" />' . "\n" .
                    '        <meta http-equiv="refresh" content="0;url=/login" />' . "\n" .
                    '' . "\n" .
                    '        <title>Redirecting to /login</title>' . "\n" .
                    '    </head>' . "\n" .
                    '    <body>' . "\n" .
                    '        Redirecting to <a href="/login">/login</a>.' . "\n" .
                    '    </body>' . "\n" .
                    '</html>'
                ),
                'https://example.com/',
            ],
            [
                [
                    Fixtures\Config::class,
                ],
                '',
                404,
                '404 Not Found',
                'https://example.com/not-here',
            ],
            [
                [
                    Fixtures\Config::class,
                ],
                '',
                405,
                'Method Not Allowed',
                'https://example.com/?loggedin',
                'POST',
            ],
        ];
    }

    public function DataProviderVerifyHandler() : Generator
    {
        foreach ($this->DataProviderHandler() as $args) {
            list ($sources, $prefix, $expectedStatus, $expectedContent, $uri) = $args;

            $yield = [
                $sources,
                $prefix,
                $expectedStatus,
                $expectedContent,
                $uri,
                'GET',
                [],
                [],
                [],
                [],
                null
            ];

            $j = count($args);

            for ($i = 5; $i < $j; $i += 1) {
                $yield[$i] = $args[$i];
            }

            yield $yield;
        }
    }

    /**
    * @dataProvider DataProviderGoodSources
    */
    public function testSources(string $className) : void
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
    public function testCompilerVerifyAddRouteAddsRoutes(string $className) : void
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

        $this->assertTrue(Dispatcher::FOUND === $present[0]);
        $this->assertTrue(Dispatcher::FOUND === $notPresent[0]);

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

    /**
    * @depends testCompilerVerifyAddRouteAddsRoutes
    * @depends testCompilerVerifyAddMiddlewareAddsMiddlewares
    * @depends testCompilerExcludesMiddleware
    *
    * @dataProvider DataProviderVerifyHandler
    */
    public function testHandler(
        array $sources,
        string $prefix,
        int $expectedStatus,
        string $expectedContent,
        string $uri,
        string $method = 'GET',
        array $parameters = [],
        array $cookies = [],
        array $files = [],
        array $server = [],
        $content = null
    ) : void {
        $dispatcher = Fixtures\Compiler::ObtainCompiler()->ObtainSimpleDispatcher(
            [],
            ...$sources
        );

        $request = Request::create(
            $uri,
            $method,
            $parameters,
            $cookies,
            $files,
            $server,
            $content
        );

        $response = BaseCompiler::Handle($dispatcher, $request, $prefix);

        $this->assertSame($expectedStatus, $response->getStatusCode());
        $this->assertSame($expectedContent, $response->getContent());
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
}
