<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftRouter\Tests;

use FastRoute\DataGenerator\GroupCountBased;
use FastRoute\RouteParser\Std;
use Generator;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase as Base;
use RuntimeException;
use SignpostMarv\DaftRouter\DaftMiddleware;
use SignpostMarv\DaftRouter\DaftRoute;
use SignpostMarv\DaftRouter\DaftSource;
use SignpostMarv\DaftRouter\ResponseException;
use SignpostMarv\DaftRouter\Router\Compiler;
use SignpostMarv\DaftRouter\Router\Dispatcher;
use SignpostMarv\DaftRouter\Router\RouteCollector;
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

            foreach (static::YieldRoutesFromSource($source) as $route) {
                yield [$route];
            }
        }
    }

    public function DataProviderMiddleware() : Generator
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

            foreach (static::YieldMiddlewareFromSource($source) as $middleware) {
                yield [$middleware];
            }
        }
    }

    public function DataProviderRoutesWithNoArgs() : Generator
    {
        $parser = new Std();
        foreach ($this->DataProviderRoutes() as $args) {
            list($route) = $args;
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

    public function DataProviderVerifyHandlerGood() : Generator
    {
        yield from $this->DataProviderVerifyHandler(true);
    }

    public function DataProviderVerifyHandlerBad() : Generator
    {
        yield from $this->DataProviderVerifyHandler(false);
    }

    public function DataProviderUriReplacement() : array
    {
        return [
            [
                'asdf',
                '',
                '/asdf',
            ],
            [
                '/asdf',
                '',
                '/asdf',
            ],
            [
                '/asdf//asdf/asdfasdf//asdf//',
                '',
                '/asdf/asdf/asdfasdf/asdf/',
            ],
        ];
    }

    /**
    * @dataProvider DataProviderUriReplacement
    */
    public function testUriReplacement(
        string $uri,
        string $prefix,
        string $expected
    ) : void {
        $this->assertSame(
            $expected,
            str_replace(
                '//',
                '/',
                '/' . preg_replace(
                    ('/^' . preg_quote($prefix, '/') . '/'),
                    '',
                    parse_url($uri, PHP_URL_PATH)
                )
            )
        );
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
    * @dataProvider DataProviderRoutes
    */
    public function testRoutes(string $className) : void
    {
        $routes = $className::DaftRouterRoutes();

        foreach (array_keys($routes) as $uri) {
            $this->assertSame(
                '/',
                mb_substr($uri, 0, 1),
                'All route uris must begin with a forward slash!'
            );
            $this->assertInternalType(
                'array',
                $routes[$uri],
                'All route uris must be specified with an array of HTTP methods!'
            );

            foreach ($routes[$uri] as $k => $v) {
                $this->assertInternalType(
                    'integer',
                    $k,
                    'All http methods must be specified with numeric indices!'
                );
                $this->assertInternalType(
                    'string',
                    $v,
                    'All http methods must be specified as an array of strings!'
                );
            }
        }
    }

    /**
    * @depends testRoutes
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
            Compiler::class,
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
            Compiler::class,
            'AddMiddleware',
            DaftMiddleware::class
        ));

        $compiler->AddMiddleware('stdClass');
    }

    /**
    * @depends testSources
    *
    * @dataProvider DataProviderMiddleware
    */
    public function testMiddlware(string $className) : void
    {
        foreach (array_values($className::DaftRouterRoutePrefixExceptions()) as $uriPrefix) {
            $this->assertSame(
                '/',
                mb_substr($uriPrefix, 0, 1),
                'All middleware uri prefixes must begin with a forward slash!'
            );
        }
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

        $dispatcher = Fixtures\Compiler::ObtainCompiler()->ObtainDispatcher(
            [
                'cacheDisabled' => true,
                'cacheFile' => tempnam(sys_get_temp_dir(), static::class),
                'dispatcher' => Dispatcher::class,
            ],
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

        $route = array_pop($present[1]);

        $this->assertInternalType(
            'string',
            $route,
            'Last entry from a dispatcher should be a string'
        );
        $this->assertTrue(is_a($route, DaftRoute::class, true), sprintf(
            'Last entry from a dispatcher should be %s',
            DaftRoute::class
        ));

        if (count($present[1]) > 0) {
            foreach ($present[1] as $middleware) {
                $this->assertInternalType(
                    'string',
                    $middleware,
                    'Leading entries from a dispatcher should be a string'
                );
                $this->assertTrue(is_a($middleware, DaftMiddleware::class, true), sprintf(
                    'Leading entries from a dispatcher should be %s',
                    DaftMiddleware::class
                ));
            }
        }
    }

    /**
    * @depends testCompilerVerifyAddRouteAddsRoutes
    * @depends testCompilerVerifyAddMiddlewareAddsMiddlewares
    * @depends testCompilerExcludesMiddleware
    *
    * @dataProvider DataProviderVerifyHandlerGood
    */
    public function testHandlerGood(
        array $sources,
        string $prefix,
        int $expectedStatus,
        string $expectedContent,
        array $requestArgs
    ) : void {
        /**
        * @var Dispatcher $dispatcher
        */
        $dispatcher = Fixtures\Compiler::ObtainCompiler()->ObtainDispatcher(
            [
                'cacheDisabled' => true,
                'cacheFile' => tempnam(sys_get_temp_dir(), static::class),
                'dispatcher' => Dispatcher::class,
            ],
            ...$sources
        );

        $this->assertInstanceOf(Dispatcher::class, $dispatcher);

        $request = Request::create(
            ...$requestArgs
        );

        $response = $dispatcher->handle($request, $prefix);

        $this->assertSame($expectedStatus, $response->getStatusCode());
        $this->assertSame($expectedContent, $response->getContent());
    }

    /**
    * @depends testCompilerVerifyAddRouteAddsRoutes
    * @depends testCompilerVerifyAddMiddlewareAddsMiddlewares
    * @depends testCompilerExcludesMiddleware
    *
    * @dataProvider DataProviderVerifyHandlerBad
    */
    public function testHandlerBad(
        array $sources,
        string $prefix,
        int $expectedStatus,
        string $expectedContent,
        array $requestArgs
    ) : void {
        /**
        * @var Dispatcher $dispatcher
        */
        $dispatcher = Fixtures\Compiler::ObtainCompiler()->ObtainDispatcher(
            [
                'cacheDisabled' => true,
                'cacheFile' => tempnam(sys_get_temp_dir(), static::class),
                'dispatcher' => Dispatcher::class,
            ],
            ...$sources
        );

        $this->assertInstanceOf(Dispatcher::class, $dispatcher);

        $request = Request::create(
            ...$requestArgs
        );

        $this->expectException(ResponseException::class);
        $this->expectExceptionCode($expectedStatus);
        $this->expectExceptionMessage($expectedContent);

        $response = $dispatcher->handle($request, $prefix);
    }

    public function DataProviderRouteCollectorAddRouteThrowsException() : Generator
    {
        yield from [
            [
                'GET',
                '/',
                [],
                InvalidArgumentException::class,
                sprintf(
                    'Cannot call %s::%s without a trailing implementation of %s',
                    RouteCollector::class,
                    'addRouteStrict',
                    DaftRoute::class
                ),
                null,
            ],
            [
                'GET',
                '/',
                [
                    InvalidArgumentException::class,
                ],
                InvalidArgumentException::class,
                sprintf(
                    'Cannot call %s::%s without a trailing implementation of %s',
                    RouteCollector::class,
                    'addRouteStrict',
                    DaftRoute::class
                ),
                null,
            ],
            [
                'GET',
                '/',
                [
                    Fixtures\NotLoggedIn::class,
                ],
                InvalidArgumentException::class,
                sprintf(
                    'Cannot call %s::%s without a trailing implementation of %s',
                    RouteCollector::class,
                    'addRouteStrict',
                    DaftRoute::class
                ),
                null,
            ],
        ];
    }

    /**
    * @dataProvider DataProviderRouteCollectorAddRouteThrowsException
    */
    public function testRouteCollectorAddRouteThrowsException(
        string $httpMethod,
        string $route,
        array $handler,
        string $expectedExceptionClass,
        ? string $expectedExceptionMessage,
        ? int $expectedExceptionCode
    ) : void {
        $collector = new RouteCollector(new Std(), new GroupCountBased());

        $this->expectException($expectedExceptionClass);

        if ( ! is_null($expectedExceptionMessage)) {
            $this->expectExceptionMessage($expectedExceptionMessage);
        }

        if ( ! is_null($expectedExceptionCode)) {
            $this->expectExceptionCode($expectedExceptionCode);
        }

        $collector->addRoute($httpMethod, $route, $handler);
    }

    protected function DataProviderVerifyHandler(bool $good = true) : Generator
    {
        $argsSource = $good ? $this->DataProviderGoodHandler() : $this->DataProviderBadHandler();
        foreach ($argsSource as $args) {
            list($sources, $prefix, $expectedStatus, $expectedContent, $uri) = $args;

            $yield = [
                $sources,
                $prefix,
                $expectedStatus,
                $expectedContent,
                array_merge(
                    [
                $uri,
                    ],
                    array_slice($args, 5)
                ),
            ];

            yield $yield;
        }
    }

    protected function DataProviderGoodHandler() : Generator
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
        ];
    }

    protected function DataProviderBadHandler() : Generator
    {
        yield from  [
            [
                [
                    Fixtures\Config::class,
                ],
                '',
                404,
                'Dispatcher was not able to generate a response!',
                'https://example.com/not-here',
            ],
            [
                [
                    Fixtures\Config::class,
                ],
                '',
                405,
                'Dispatcher was not able to generate a response!',
                'https://example.com/?loggedin',
                'POST',
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
}
