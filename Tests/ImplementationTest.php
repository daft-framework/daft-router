<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftRouter\Tests;

use BadMethodCallException;
use Generator;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase as Base;
use RuntimeException;
use SignpostMarv\DaftRouter\DaftRequestInterceptor;
use SignpostMarv\DaftRouter\DaftResponseModifier;
use SignpostMarv\DaftRouter\DaftRoute;
use SignpostMarv\DaftRouter\DaftRouteFilter;
use SignpostMarv\DaftRouter\DaftSource;
use SignpostMarv\DaftRouter\EmptyArgs;
use SignpostMarv\DaftRouter\ResponseException;
use SignpostMarv\DaftRouter\Router\Compiler;
use SignpostMarv\DaftRouter\Router\Dispatcher;
use Symfony\Component\HttpFoundation\Request;
use Throwable;

class ImplementationTest extends Base
{
    /**
    * @psalm-return Generator<int, array{0:class-string<DaftSource>}, mixed, void>
    */
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

    /**
    * @psalm-return Generator<int, array{0:class-string<DaftRouteFilter>, 1:class-string<DaftRoute>, 2:string, 3:string, 4:class-string<DaftRoute>, 5:string, 6:string}, mixed, void>
    */
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

    /**
    * @psalm-return Generator<int, array{0:class-string<DaftRoute>}, mixed, void>
    */
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

            foreach (static::YieldRoutesFromSource($source) as $route) {
                yield [$route];
            }
        }
    }

    /**
    * @psalm-return Generator<int, array{0:class-string<DaftRouteFilter>}, mixed, void>
    */
    public function DataProviderMiddleware() : Generator
    {
        foreach ($this->DataProviderGoodSources() as $i => $args) {
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

    /**
    * @psalm-return Generator<int, array{0:class-string<DaftRoute>, 1:array<string, string>, 2:array<string, mixed>, 3:string, 4:string, 5?:class-string<Throwable>, 6?:string}, mixed, void>
    */
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
        ];
    }

    /**
    * @psalm-return Generator<int, array{0:class-string<DaftSource>[], 1:string, 2:int, 3:string, 4:string[], 5:array<string, scalar|array|object|null>}, mixed, void>
    */
    public function DataProviderVerifyHandlerGood() : Generator
    {
        yield from $this->DataProviderVerifyHandler(true);
    }

    /**
    * @psalm-return Generator<int, array{0:class-string<DaftSource>[], 1:string, 2:int, 3:string, 4:string[], 5:array<string, scalar|array|object|null>}, mixed, void>
    */
    public function DataProviderVerifyHandlerBad() : Generator
    {
        yield from $this->DataProviderVerifyHandler(false);
    }

    /**
    * @return string[][]
    *
    * @psalm-return array<int, array{0:string, 1:string, 2:string}>
    */
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
    * @return mixed[][]
    *
    * @psalm-return array<int, array{0:mixed}>
    */
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

        /**
        * @var array
        */
        $sources = $sources;

        if (count($sources) < 1) {
            static::markTestSkipped('No sources to test!');
        } else {
            $initialCount = count($sources);

            /**
            * @var array<int, mixed>
            */
            $sources = array_filter($sources, 'is_int', ARRAY_FILTER_USE_KEY);

            static::assertCount(
                $initialCount,
                $sources,
                'DaftSource::DaftRouterRouteAndMiddlewareSources() must be of the form array<int, mixed>'
            );

            /**
            * @var array<int, string>
            */
            $sources = array_filter($sources, 'is_string');

            static::assertCount(
                $initialCount,
                $sources,
                'DaftSource::DaftRouterRouteAndMiddlewareSources() must be of the form array<int, string>'
            );

            /**
            * @var int
            */
            $prevKey = key($sources);

            /**
            * @var array<int, int>
            */
            $sourceKeys = array_keys($sources);

            foreach ($sourceKeys as $i => $k) {
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

                /**
                * @psalm-var class-string
                */
                $source = $sources[$k];

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

        $routes = $className::DaftRouterRoutes();

        $initialCount = count($routes);

        /**
        * @var array<string, mixed>
        */
        $routes = array_filter($routes, 'is_string', ARRAY_FILTER_USE_KEY);

        static::assertCount(
            $initialCount,
            $routes,
            'DaftRoute::DaftRouterRoutes() must be of the form array<string, mixed>'
        );

        $routes = array_filter(
            $routes,
            function (string $uri) : bool {
                return 1 === preg_match('/^(?:\/|{[a-z][a-z0-9]*:\/)/', $uri);
            },
            ARRAY_FILTER_USE_KEY
        );

        static::assertCount(
            $initialCount,
            $routes,
            'All route uris must begin with a forward slash, or an argument that begins with such!'
        );

        /**
        * @var array<string, array>
        */
        $routes = array_filter($routes, 'is_array');

        static::assertCount(
            $initialCount,
            $routes,
            'DaftRoute::DaftRouterRoutes() must be of the form array<string, array>'
        );

        foreach ($routes as $routesToCheck) {
            $initialCount = count($routesToCheck);

            static::assertGreaterThan(0, $initialCount, 'URIs must have at least one method!');

            $routesToCheck = array_filter($routesToCheck, 'is_int', ARRAY_FILTER_USE_KEY);

            static::assertCount(
                $initialCount,
                $routesToCheck,
                'DaftRoute::DaftRouterRoutes() must be of the form array<string, array<int, mixed>>'
            );

            $routesToCheck = array_filter($routesToCheck, 'is_string');

            static::assertCount(
                $initialCount,
                $routesToCheck,
                'DaftRoute::DaftRouterRoutes() must be of the form array<string, array<int, string>>'
            );
        }
    }

    /**
    * @param class-string<DaftRoute> $className
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
        string $expectedRouteResult
    ) : void {
        $typed_args_object = $className::DaftRouterHttpRouteArgsTyped($args, $method);

        static::assertSame($typedArgs, $typed_args_object->toArray());
        static::assertSame(
            $expectedRouteResult,
            $className::DaftRouterHttpRoute($typed_args_object, $method)
        );
    }

    public function testCompilerVerifyAddRouteThrowsException() : void
    {
        $compiler = Fixtures\Compiler::ObtainCompiler();

        $compiler->NudgeCompilerWithRouteOrRouteFilter('stdClass');

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

        /**
        * @var string
        */
        foreach (static::YieldRoutesFromSource($className) as $route) {
            $routes[] = $route;
            $compiler->NudgeCompilerWithRouteOrRouteFilter($route);
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

        $initialCount = count($uriPrefixes);

        /**
        * @var string[]
        */
        $uriPrefixes = array_filter($uriPrefixes, 'is_string');

        static::assertCount(
            $initialCount,
            $uriPrefixes,
            'DaftRouteFilter::DaftRouterRoutePrefixExceptions() must return a list of strings!'
        );

        foreach ($uriPrefixes as $uriPrefix) {
            static::assertSame(
                '/',
                mb_substr($uriPrefix, 0, 1),
                'All middleware uri prefixes must begin with a forward slash!'
            );
        }
    }

    /**
    * @psalm-param class-string<DaftRequestInterceptor>|class-string<DaftResponseModifier>|class-string<DaftSource> $className
    *
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

    /**
    * @psalm-param class-string<DaftRouteFilter> $middleware
    * @psalm-param class-string<DaftRoute> $presentWith
    * @psalm-param class-string<DaftRoute> $notPresentWith
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
        *
        * @psalm-var class-string<DaftRoute>|false
        */
        $route = array_pop($dispatchedPresent);

        /**
        * @var array
        */
        $dispatchedPresent = $dispatchedPresent;

        static::assertIsString(
            $route,
            'Last entry from a dispatcher should be a string'
        );

        /**
        * @psalm-var class-string<DaftRoute>
        */
        $route = $route;

        static::assertTrue(is_a($route, DaftRoute::class, true), sprintf(
            'Last entry from a dispatcher should be %s',
            DaftRoute::class
        ));

        static::assertCount(2, $dispatchedPresent);
        static::assertTrue(isset($dispatchedPresent[DaftRequestInterceptor::class]));
        static::assertTrue(isset($dispatchedPresent[DaftResponseModifier::class]));
        static::assertIsArray($dispatchedPresent[DaftRequestInterceptor::class]);
        static::assertIsArray($dispatchedPresent[DaftResponseModifier::class]);

        /**
        * @var array
        */
        $interceptors = $dispatchedPresent[DaftRequestInterceptor::class];

        /**
        * @var array
        */
        $modifiers = $dispatchedPresent[DaftResponseModifier::class];

        $initialCount = count($interceptors);

        /**
        * @var string[]
        */
        $interceptors = array_filter($interceptors, 'is_string');

        static::assertCount($initialCount, $interceptors);
        static::assertSame(array_values($interceptors), $interceptors);

        /**
        * @var array<int, string>
        */
        $interceptors = array_values($interceptors);

        $initialCount = count($modifiers);

        /**
        * @var string[]
        */
        $modifiers = array_filter($modifiers, 'is_string');

        static::assertCount($initialCount, $modifiers);
        static::assertSame(array_values($modifiers), $modifiers);

        /**
        * @var array<int, string>
        */
        $modifiers = array_values($modifiers);

        foreach ($interceptors as $interceptor) {
            static::assertTrue(is_a($interceptor, DaftRequestInterceptor::class, true), sprintf(
                'Leading entries from a dispatcher should be %s',
                DaftRequestInterceptor::class
            ));
        }

        foreach ($modifiers as $modifier) {
            static::assertTrue(is_a($modifier, DaftResponseModifier::class, true), sprintf(
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

        $dispatcher->handle($request, $prefix);
    }

    public function testDaftRouterAutoMethodCheckingTraitFails() : void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Specified method not supported!');

        Fixtures\Profile::DaftRouterHttpRoute(new Fixtures\IntIdArgs(['id' => '1']), 'POST');
    }

    public function testImmutabilityOfTypedArgs() : void
    {
        $object = new Fixtures\IntIdArgs(['id' => '1']);

        static::assertSame(1, $object->id);

        static::expectException(BadMethodCallException::class);
        static::expectExceptionMessage(
            Fixtures\IntIdArgs::class .
            '::$id is not writeable, cannot be set to 2'
        );

        $object->id = 2;
    }

    public function testImmutabilityOfEmptyArgs() : void
    {
        $object = new EmptyArgs();

        static::assertCount(0, $object);

        static::expectException(BadMethodCallException::class);
        static::expectExceptionMessage(
            EmptyArgs::class .
            '::__get() cannot be called on ' .
            EmptyArgs::class .
            ' with foo, ' .
            EmptyArgs::class .
            ' has no arguments!'
        );

        /**
        * @psalm-suppress InvalidScalarArgument
        */
        $object->foo;
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

    /**
    * @psalm-return Generator<int, array{0:class-string<DaftSource>[], 1:string, 2:int, 3:string, 4:string[], 5:array<string, scalar|array|object|null>}, mixed, void>
    */
    protected function DataProviderVerifyHandler(bool $good = true) : Generator
    {
        $argsSource = $good ? $this->DataProviderGoodHandler() : $this->DataProviderBadHandler();
        /**
        * @var mixed[]
        */
        foreach ($argsSource as $args) {
            list($sources, $prefix, $expectedStatus, $expectedContent, $headers, $uri) = $args;

            /**
            * @psalm-var array{0:class-string<DaftSource>[], 1:string, 2:int, 3:string, 4:string[], 5:array<string, scalar|array|object|null>}
            */
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

    /**
    * @psalm-return Generator<int, class-string<DaftRoute>, mixed, void>
    */
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

    /**
    * @psalm-param class-string<DaftRequestInterceptor>|class-string<DaftResponseModifier>|class-string<DaftSource> $source
    *
    * @psalm-return Generator<int, class-string<DaftRequestInterceptor>|class-string<DaftResponseModifier>, mixed, void>
    */
    protected static function YieldMiddlewareFromSource(string $source) : Generator
    {
        if (
            is_a($source, DaftRequestInterceptor::class, true) ||
            is_a($source, DaftResponseModifier::class, true)
        ) {
            yield $source;
        }
        if (is_a($source, DaftSource::class, true)) {
            foreach ($source::DaftRouterRouteAndMiddlewareSources() as $otherSource) {
                if (
                    is_a($otherSource, DaftRequestInterceptor::class, true) ||
                    is_a($otherSource, DaftResponseModifier::class, true) ||
                    is_a($otherSource, DaftSource::class, true)
                ) {
                yield from static::YieldMiddlewareFromSource($otherSource);
                }
            }
        }
    }
}
