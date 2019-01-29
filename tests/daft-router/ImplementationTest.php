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
use RuntimeException;
use SignpostMarv\DaftRouter\DaftRequestInterceptor;
use SignpostMarv\DaftRouter\DaftResponseModifier;
use SignpostMarv\DaftRouter\DaftRoute;
use SignpostMarv\DaftRouter\DaftRouteFilter;
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
                Fixtures\ConfigNoModify::class,
            ],
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
            [
                Fixtures\AdminNotLoggedIn::class,
                Fixtures\AdminHome::class,
                'GET',
                '/admin',
                Fixtures\Login::class,
                'GET',
                '/admin/login',
            ],
        ];
    }

    public function DataProviderRoutes() : Generator
    {
        /**
        * @var string[]|null
        */
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

            /**
            * @var string
            */
            foreach (static::YieldRoutesFromSource($source) as $route) {
                yield [$route];
            }
        }
    }

    public function DataProviderMiddleware() : Generator
    {
        /**
        * @var string[]|null
        */
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

            /**
            * @var string
            */
            foreach (static::YieldMiddlewareFromSource($source) as $middleware) {
                yield [$middleware];
            }
        }
    }

    public function DataProviderRoutesWithNoArgs() : Generator
    {
        $parser = new Std();
        /**
        * @psalm-var iterable<array{0:class-string<DaftRoute>}> $argsSource
        */
        $argsSource = $this->DataProviderRoutes();

        foreach ($argsSource as $args) {
            list($route) = $args;

            if ( ! is_a($route, DaftRoute::class, true)) {
                static::assertTrue(
                    is_a($route, DaftRoute::class, true),
                    sprintf(
                        'Source must be an implementation of %s, "%s" given.',
                        DaftRoute::class,
                        $route
                    )
                );
            }

            /**
            * @var string
            * @var array<int, string> $uris
            */
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

    public function DataProviderRoutesWithKnownArgs() : Generator
    {
        yield from [
            [
                Fixtures\Profile::class,
                ['id' => '1'],
                ['id' => 1],
                'GET',
                '/profile/1',
            ],
            [
                Fixtures\Profile::class,
                [
                    'id' => '1',
                    'slug' => 'foo',
                ],
                [
                    'id' => 1,
                    'slug' => 'foo',
                ],
                'GET',
                '/profile/1~foo',
            ],
            [
                Fixtures\Home::class,
                [],
                [],
                'GET',
                '/',
            ],
            [
                Fixtures\Home::class,
                [],
                [],
                'GET',
                '/',
                InvalidArgumentException::class,
                'This route takes no arguments!',
            ],
        ];
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

    public function DataProviderEnsureDispatcherIsCorrectlyTypedPublic() : array
    {
        return [
            ['0'],
            [1],
            [2.0],
            [[3, 3, 3]],
            [new \stdClass()],
            [null],
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
        static::assertSame(
            $expected,
            str_replace(
                '//',
                '/',
                '/' . preg_replace(
                    ('/^' . preg_quote($prefix, '/') . '/'),
                    '',
                    (string) parse_url($uri, PHP_URL_PATH)
                )
            )
        );
    }

    /**
    * @psalm-param class-string<DaftSource> $className
    *
    * @dataProvider DataProviderGoodSources
    */
    public function testSources(string $className) : void
    {
        if ( ! is_a($className, DaftSource::class, true)) {
            static::assertTrue(
                is_a($className, DaftSource::class, true),
                sprintf(
                    'Source must be an implementation of %s, "%s" given.',
                    DaftSource::class,
                    $className
                )
            );
        }

        /**
        * @var scalar|array|object|null
        */
        $sources = $className::DaftRouterRouteAndMiddlewareSources();

        static::assertIsArray($sources);

        $sources = (array) $sources;

        if (count($sources) < 1) {
            static::markTestSkipped('No sources to test!');
        } else {
            /**
            * @var int|false
            */
            $prevKey = key($sources);

            /**
            * @var array<int, int|string>
            */
            $sourceKeys = array_keys($sources);

            foreach ($sourceKeys as $i => $k) {
                /*
                * this is inside here because of a bug in phpstan/phpstan or phpstan/phpstan-phpunit
                */
                static::assertIsInt(
                    $prevKey,
                    'Sources must be listed with integer keys!'
                );

                $prevKey = (int) $prevKey;

                static::assertIsInt($k, 'Sources must be listed with integer keys!');

                $k = (int) $k;

                if ($i > 0) {
                    static::assertGreaterThan(
                        $prevKey,
                        $k,
                        'Sources must be listed with incremental keys!'
                    );
                    static::assertSame(
                        $prevKey + 1,
                        $k,
                        'Sources must be listed with sequential keys!'
                    );
                }

                static::assertIsString($sources[$k]);

                $source = (string) $sources[$k];

                static::assertTrue(
                    (
                        is_a($source, DaftSource::class, true) ||
                        is_a($source, DaftRoute::class, true) ||
                        is_a($source, DaftRouteFilter::class, true)
                    ),
                    sprintf(
                        'Sources must only be listed as routes, middleware or sources! (%s)',
                        $source
                    )
                );

                $prevKey = $k;
            }
        }
    }

    /**
    * @psalm-param class-string<DaftRoute> $className
    *
    * @depends testSources
    *
    * @dataProvider DataProviderRoutes
    */
    public function testRoutes(string $className) : void
    {
        if ( ! is_a($className, DaftRoute::class, true)) {
            static::assertTrue(
                is_a($className, DaftRoute::class, true),
                sprintf(
                    'Source must be an implementation of %s, "%s" given.',
                    DaftRoute::class,
                    $className
                )
            );
        }

        /**
        * @var array<int|string, scalar[]>
        */
        $routes = (array) $className::DaftRouterRoutes();

        foreach ($routes as $uri => $routesToCheck) {
            static::assertIsString($uri, 'route keys must be strings!');

            $uri = (string) $uri;

            static::assertSame(
                '/',
                mb_substr($uri, 0, 1),
                'All route uris must begin with a forward slash!'
            );

            static::assertIsArray(
                $routesToCheck,
                'All route uris must be specified with an array of HTTP methods!'
            );

            foreach ($routesToCheck as $k => $v) {
                static::assertIsInt(
                    $k,
                    'All http methods must be specified with numeric indices!'
                );
                static::assertIsString(
                    $v,
                    'All http methods must be specified as an array of strings!'
                );
            }
        }
    }

    /**
    * @psalm-param class-string<DaftRoute> $className
    *
    * @depends testRoutes
    *
    * @dataProvider DataProviderRoutesWithNoArgs
    */
    public function testRoutesWithNoArgs(string $className, string $method) : void
    {
        if ( ! is_a($className, DaftRoute::class, true)) {
            static::assertTrue(
                is_a($className, DaftRoute::class, true),
                sprintf(
                    'Source must be an implementation of %s, "%s" given.',
                    DaftRoute::class,
                    $className
                )
            );
        }

        $this->expectException(InvalidArgumentException::class);
        $className::DaftRouterHttpRoute(['foo' => 'bar'], $method);
    }

    /**
    * @psalm-param class-string<DaftRoute> $className
    *
    * @param array<string, string> $args
    *
    * @depends testRoutes
    *
    * @dataProvider DataProviderRoutesWithKnownArgs
    */
    public function testRoutesWithArgs(
        string $className,
        array $args,
        array $typedArgs,
        string $method,
        string $expectedRouteResult,
        string $expectedExceptionClassWithArgs = null,
        string $expectedExceptionMessageWithArgs = null
    ) : void {
        if ( ! is_a($className, DaftRoute::class, true)) {
            static::assertTrue(
                is_a($className, DaftRoute::class, true),
                sprintf(
                    'Source must be an implementation of %s, "%s" given.',
                    DaftRoute::class,
                    $className
                )
            );
        }

        static::assertSame($typedArgs, $className::DaftRouterHttpRouteArgsTyped($args, $method));
        static::assertSame($expectedRouteResult, $className::DaftRouterHttpRoute($args, $method));

        if (
            is_string($expectedExceptionClassWithArgs) &&
            is_string($expectedExceptionMessageWithArgs)
        ) {
            static::expectException($expectedExceptionClassWithArgs);
            static::expectExceptionMessage($expectedExceptionMessageWithArgs);

            $className::DaftRouterHttpRouteArgsTyped(['foo' => 'bar'], $method);
        }
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

    public function testCompilerVerifyAddRouteThrowsExceptionWithHandler() : void
    {
        $collector = new RouteCollector(new Std(), new GroupCountBased());

        $collector->addRoute(['GET'], '/', [Fixtures\Home::class]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(sprintf(
            'Argument 3 passed to %s::%s must be an array!',
            RouteCollector::class,
            'addRoute'
        ));

        $collector->addRoute('GET', '', '');
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

        /**
        * @var string
        */
        foreach (static::YieldRoutesFromSource($className) as $route) {
            $routes[] = $route;
            $compiler->AddRoute($route);
        }

        static::assertSame($routes, $compiler->ObtainRoutes());
    }

    public function testCompilerVerifyAddMiddlewareThrowsException() : void
    {
        $compiler = Fixtures\Compiler::ObtainCompiler();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(sprintf(
            'Argument 1 passed to %s::%s must be an implementation of %s',
            Compiler::class,
            'AddMiddleware',
            DaftRouteFilter::class
        ));

        $compiler->AddMiddleware('stdClass');
    }

    /**
    * @param mixed $maybe
    *
    * @dataProvider DataProviderEnsureDispatcherIsCorrectlyTypedPublic
    */
    public function testCompilerVerifyEnsureDispatcherIsCorrectlyTypedThrowsException(
        $maybe
    ) : void {
        $compiler = Fixtures\Compiler::ObtainCompiler();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(sprintf(
            'cachedDispatcher expected to return instance of %s, returned instead "%s"',
            Dispatcher::class,
            (is_object($maybe) ? get_class($maybe) : gettype($maybe))
        ));

        $compiler::EnsureDispatcherIsCorrectlyTypedPublic($maybe);
    }

    /**
    * @psalm-param class-string<DaftRouteFilter> $className
    *
    * @depends testSources
    *
    * @dataProvider DataProviderMiddleware
    */
    public function testMiddlware(string $className) : void
    {
        if ( ! is_a($className, DaftRouteFilter::class, true)) {
            static::assertTrue(
                is_a($className, DaftRouteFilter::class, true),
                sprintf(
                    'Source must be an implementation of %s, "%s" given.',
                    DaftRouteFilter::class,
                    $className
                )
            );
        }

        /**
        * @var scalar[]
        */
        $uriPrefixes = $className::DaftRouterRoutePrefixExceptions();

        foreach ($uriPrefixes as $uriPrefix) {
            static::assertIsString($uriPrefix);

            static::assertSame(
                '/',
                mb_substr((string) $uriPrefix, 0, 1),
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
        /**
        * @var string[]
        */
        $middlewares = [];
        $compiler = Fixtures\Compiler::ObtainCompiler();

        /**
        * @var string
        */
        foreach (static::YieldMiddlewareFromSource($className) as $middleware) {
            $middlewares[] = $middleware;
            $compiler->AddMiddleware($middleware);
        }

        $middlewares[] = DaftRouteFilter::class;
        $middlewares = array_filter($middlewares, function (string $middleware) : bool {
            return
                is_a($middleware, DaftRequestInterceptor::class, true) ||
                is_a($middleware, DaftResponseModifier::class, true);
        });

        static::assertSame($middlewares, $compiler->ObtainMiddleware());
    }

    /**
    * @psalm-param class-string<DaftSource> $className
    *
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

        /**
        * @var string
        */
        foreach (static::YieldRoutesFromSource($className) as $route) {
            $routes[] = $route;
        }
        /**
        * @var string
        */
        foreach (static::YieldMiddlewareFromSource($className) as $middleware) {
            $middlewares[] = $middleware;
        }

        $compiler->NudgeCompilerWithSources($className);
        static::assertSame($routes, $compiler->ObtainRoutes());
        static::assertSame($middlewares, $compiler->ObtainMiddleware());

        $compiler->NudgeCompilerWithSources($className);
        static::assertSame(
            $routes,
            $compiler->ObtainRoutes(),
            'Routes must be identical after adding a source more than once!'
        );
        static::assertSame(
            $middlewares,
            $compiler->ObtainMiddleware(),
            'Middleware must be identical after adding a source more than once!'
        );
    }

    public function testNudgeCompilerWithSourcesBad() : void
    {
        $compiler = Fixtures\Compiler::ObtainCompiler();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(
            Fixtures\BadStaticMethodCollector::class .
            ' yielded a non-string value!'
        );

        $compiler->NudgeCompilerWithSourcesBad('foo', 'bar', 'baz');
    }

    /**
    * @psalm-param class-string<DaftSource> $middleware
    * @psalm-param class-string<DaftSource> $presentWith
    * @psalm-param class-string<DaftSource> $notPresentWith
    *
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
        static::assertTrue(is_a($middleware, DaftRouteFilter::class, true));
        static::assertTrue(is_a($presentWith, DaftRoute::class, true));
        static::assertTrue(is_a($notPresentWith, DaftRoute::class, true));

        $dispatcher = Fixtures\Compiler::ObtainCompiler()::ObtainDispatcher(
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

        static::assertIsArray($present);
        static::assertIsArray($notPresent);

        static::assertTrue(Dispatcher::FOUND === $present[0]);
        static::assertTrue(Dispatcher::FOUND === $notPresent[0]);

        /**
        * @var string[]
        */
        $dispatchedPresent = $present[1];

        /**
        * @var string[]
        */
        $dispatchedNotPresent = $notPresent[1];

        $expectedWithMiddleware = [
            DaftRequestInterceptor::class => [],
            DaftResponseModifier::class => [],
            $presentWith,
        ];

        if (is_a($middleware, DaftRequestInterceptor::class, true)) {
            $expectedWithMiddleware[DaftRequestInterceptor::class][] = $middleware;
        }

        if (is_a($middleware, DaftResponseModifier::class, true)) {
            $expectedWithMiddleware[DaftResponseModifier::class][] = $middleware;
        }

        static::assertSame(
            $expectedWithMiddleware,
            $dispatchedPresent
        );
        static::assertSame(
            [
                DaftRequestInterceptor::class => [],
                DaftResponseModifier::class => [],
                $notPresentWith,
            ],
            $dispatchedNotPresent
        );

        /**
        * @var string|false
        */
        $route = array_pop($dispatchedPresent);

        static::assertIsString(
            $route,
            'Last entry from a dispatcher should be a string'
        );

        static::assertTrue(is_a((string) $route, DaftRoute::class, true), sprintf(
            'Last entry from a dispatcher should be %s',
            DaftRoute::class
        ));
        static::assertIsArray($dispatchedPresent);
        static::assertCount(2, $dispatchedPresent);
        static::assertTrue(isset($dispatchedPresent[DaftRequestInterceptor::class]));
        static::assertTrue(isset($dispatchedPresent[DaftResponseModifier::class]));
        static::assertIsArray($dispatchedPresent[DaftRequestInterceptor::class]);
        static::assertIsArray($dispatchedPresent[DaftResponseModifier::class]);

        /**
        * @var array
        */
        $interceptor = $dispatchedPresent[DaftRequestInterceptor::class];

        /**
        * @var array
        */
        $modifier = $dispatchedPresent[DaftResponseModifier::class];

        $initialCount = count($interceptor);

        /**
        * @var string[]
        */
        $interceptor = array_filter($interceptor, 'is_string');

        static::assertCount($initialCount, $interceptor);
        static::assertSame(array_values($interceptor), $interceptor);

        /**
        * @var array<int, string>
        */
        $interceptor = array_values($interceptor);

        $initialCount = count($modifier);

        /**
        * @var string[]
        */
        $modifier = array_filter($modifier, 'is_string');

        static::assertCount($initialCount, $modifier);
        static::assertSame(array_values($modifier), $modifier);

        /**
        * @var array<int, string>
        */
        $modifier = array_values($modifier);

        foreach ($interceptor as $middleware) {
            static::assertTrue(is_a($middleware, DaftRequestInterceptor::class, true), sprintf(
                'Leading entries from a dispatcher should be %s',
                DaftRequestInterceptor::class
            ));
        }

        foreach ($modifier as $middleware) {
            static::assertTrue(is_a($middleware, DaftResponseModifier::class, true), sprintf(
                'Leading entries from a dispatcher should be %s',
                DaftResponseModifier::class
            ));
        }
    }

    /**
    * @psalm-param class-string<DaftSource>[] $sources
    *
    * @depends testCompilerVerifyAddRouteAddsRoutes
    * @depends testCompilerVerifyAddMiddlewareAddsMiddlewares
    * @depends testCompilerExcludesMiddleware
    *
    * @dataProvider DataProviderVerifyHandlerGood
    *
    * @param string[] $sources
    * @param array<string, scalar|array|object|null> $expectedHeaders
    */
    public function testHandlerGood(
        array $sources,
        string $prefix,
        int $expectedStatus,
        string $expectedContent,
        array $requestArgs,
        array $expectedHeaders = []
    ) : void {
        /**
        * @var Dispatcher
        */
        $dispatcher = Fixtures\Compiler::ObtainCompiler()::ObtainDispatcher(
            [
                'cacheDisabled' => true,
                'cacheFile' => tempnam(sys_get_temp_dir(), static::class),
                'dispatcher' => Dispatcher::class,
            ],
            ...$sources
        );

        $request = static::ReqeuestFromArgs($requestArgs);

        $response = $dispatcher->handle($request, $prefix);

        static::assertSame($expectedStatus, $response->getStatusCode());
        static::assertSame($expectedContent, $response->getContent());

        foreach ($expectedHeaders as $header => $value) {
            static::assertSame($response->headers->get($header), $value);
        }
    }

    /**
    * @psalm-param class-string<DaftSource>[] $sources
    *
    * @depends testCompilerVerifyAddRouteAddsRoutes
    * @depends testCompilerVerifyAddMiddlewareAddsMiddlewares
    * @depends testCompilerExcludesMiddleware
    *
    * @dataProvider DataProviderVerifyHandlerBad
    *
    * @param string[] $sources
    */
    public function testHandlerBad(
        array $sources,
        string $prefix,
        int $expectedStatus,
        string $expectedContent,
        array $requestArgs
    ) : void {
        $dispatcher = Fixtures\Compiler::ObtainCompiler()::ObtainDispatcher(
            [
                'cacheDisabled' => true,
                'cacheFile' => tempnam(sys_get_temp_dir(), static::class),
                'dispatcher' => Dispatcher::class,
            ],
            ...$sources
        );

        $request = static::ReqeuestFromArgs($requestArgs);

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

    protected static function ReqeuestFromArgs(array $requestArgs) : Request
    {
        $uri = (string) $requestArgs[0];
        $method = 'GET';
        $parameters = [];
        $cookies = [];
        $files = [];
        $server = [];
        /**
        * @var null
        */
        $content = null;

        if (isset($requestArgs[1]) && is_string($requestArgs[1])) {
            $method = $requestArgs[1];
        }
        if (isset($requestArgs[2]) && is_array($requestArgs[2])) {
            $parameters = $requestArgs[2];
        }
        if (isset($requestArgs[3]) && is_array($requestArgs[3])) {
            $cookies = $requestArgs[3];
        }
        if (isset($requestArgs[4]) && is_array($requestArgs[4])) {
            $files = $requestArgs[4];
        }
        if (isset($requestArgs[5]) && is_array($requestArgs[5])) {
            $server = $requestArgs[5];
        }
        if (
            isset($requestArgs[6]) &&
            (is_string($requestArgs[6]) || is_resource($requestArgs[7]))
        ) {
            /**
            * @var string|resource
            */
            $content = $requestArgs[6];
        }

        return Request::create(
            $uri,
            $method,
            $parameters,
            $cookies,
            $files,
            $server,
            $content
        );
    }

    protected function DataProviderVerifyHandler(bool $good = true) : Generator
    {
        $argsSource = $good ? $this->DataProviderGoodHandler() : $this->DataProviderBadHandler();
        /**
        * @var mixed[]
        */
        foreach ($argsSource as $args) {
            list($sources, $prefix, $expectedStatus, $expectedContent, $headers, $uri) = $args;

            $yield = [
                $sources,
                $prefix,
                $expectedStatus,
                $expectedContent,
                array_merge(
                    [
                        $uri,
                    ],
                    array_slice($args, 6)
                ),
                $headers,
            ];

            yield $yield;
        }
    }

    protected function DataProviderGoodHandler() : Generator
    {
        yield from [
            [
                [
                    Fixtures\ConfigNoModify::class,
                ],
                '',
                200,
                '',
                [],
                'https://example.com/?loggedin',
            ],
            [
                [
                    Fixtures\ConfigNoModify::class,
                ],
                '/',
                200,
                '',
                [],
                'https://example.com/?loggedin',
            ],
            [
                [
                    Fixtures\ConfigNoModify::class,
                ],
                '/foo/',
                200,
                '',
                [],
                'https://example.com/foo/?loggedin',
            ],
            [
                [
                    Fixtures\ConfigNoModify::class,
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
                [],
                'https://example.com/',
            ],
            [
                [
                    Fixtures\ConfigNoModify::class,
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
                [],
                'https://example.com/',
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
                [
                    'foo' => 'bar',
                ],
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
                [],
                'https://example.com/not-here',
            ],
            [
                [
                    Fixtures\Config::class,
                ],
                '',
                405,
                'Dispatcher was not able to generate a response!',
                [],
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
            /**
            * @var string
            */
            foreach ($source::DaftRouterRouteAndMiddlewareSources() as $otherSource) {
                yield from static::YieldRoutesFromSource($otherSource);
            }
        }
    }

    protected static function YieldMiddlewareFromSource(string $source) : Generator
    {
        if (
            is_a($source, DaftRequestInterceptor::class, true) ||
            is_a($source, DaftResponseModifier::class, true)
        ) {
            yield $source;
        }
        if (is_a($source, DaftSource::class, true)) {
            /**
            * @var string
            */
            foreach ($source::DaftRouterRouteAndMiddlewareSources() as $otherSource) {
                yield from static::YieldMiddlewareFromSource($otherSource);
            }
        }
    }
}
