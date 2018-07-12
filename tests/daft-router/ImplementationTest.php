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
        ];
    }

    public function DataProviderRoutes() : Generator
    {
        /**
        * @var string[]|null $args
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
            * @var string $route
            */
            foreach (static::YieldRoutesFromSource($source) as $route) {
                yield [$route];
            }
        }
    }

    public function DataProviderMiddleware() : Generator
    {
        /**
        * @var string[]|null $args
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
            * @var string $middleware
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
        * @var string[] $args
        */
        foreach ($this->DataProviderRoutes() as $args) {
            list($route) = $args;
            /**
            * @var string $method
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
    * @dataProvider DataProviderGoodSources
    */
    public function testSources(string $className) : void
    {
        static::assertTrue(
            is_a($className, DaftSource::class, true),
            sprintf(
                'Source must be an implementation of %s, "%s" given.',
                DaftSource::class,
                $className
            )
        );

        /**
        * this is here just for vimeo/psalm.
        *
        * @var array|false
        */
        $sources = $className::DaftRouterRouteAndMiddlewareSources();

        static::assertInternalType('array', $sources);

        /**
        * this is here just for vimeo/psalm.
        *
        * @var array $sources
        */
        $sources = $sources;

        if (count($sources) < 1) {
            static::markTestSkipped('No sources to test!');
        } else {
            /**
            * @var int|false $prevKey
            */
            $prevKey = key($sources);

            /**
            * this is here just for vimeo/psalm.
            *
            * @var string|int $k
            */
            foreach (array_keys($sources) as $i => $k) {
                /*
                * this is inside here because of a bug in phpstan/phpstan or phpstan/phpstan-phpunit
                */
                static::assertInternalType(
                    'int',
                    $prevKey,
                    'Sources must be listed with integer keys!'
                );

                /**
                * this is here just for vimeo/psalm.
                *
                * @var int $prevKey
                */
                $prevKey = $prevKey;

                static::assertInternalType('int', $k, 'Sources must be listed with integer keys!');

                /**
                * this is here just for vimeo/psalm.
                *
                * @var int $k
                */
                $k = $k;

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

                static::assertInternalType('string', $sources[$k]);

                /**
                * this is here just for vimeo/psalm.
                *
                * @var string $source
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
    * @depends testSources
    *
    * @dataProvider DataProviderRoutes
    */
    public function testRoutes(string $className) : void
    {
        /**
        * this is here just for vimeo/psalm.
        *
        * @var array<string|int, array<string|int, string|false>|false> $routes
        */
        $routes = $className::DaftRouterRoutes();

        foreach (array_keys($routes) as $uri) {
            static::assertInternalType('string', $uri, 'route keys must be strings!');

            /**
            * this is here just for vimeo/psalm.
            *
            * @var string $uri
            */
            $uri = $uri;

            static::assertSame(
                '/',
                mb_substr($uri, 0, 1),
                'All route uris must begin with a forward slash!'
            );

            $routesToCheck = $routes[$uri];

            static::assertInternalType(
                'array',
                $routesToCheck,
                'All route uris must be specified with an array of HTTP methods!'
            );

            /**
            * this is here just for vimeo/psalm.
            *
            * @var array $routesToCheck
            */
            $routesToCheck = $routesToCheck;

            /**
            * this is here just for vimeo/psalm.
            *
            * @var int|string $k
            * @var string|false $v
            */
            foreach ($routesToCheck as $k => $v) {
                static::assertInternalType(
                    'integer',
                    $k,
                    'All http methods must be specified with numeric indices!'
                );
                static::assertInternalType(
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
        * @var string $route
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
    * @depends testSources
    *
    * @dataProvider DataProviderMiddleware
    */
    public function testMiddlware(string $className) : void
    {
        /**
        * this is here just for vimeo/psalm.
        *
        * @var string|false $uriPrefix
        */
        foreach ($className::DaftRouterRoutePrefixExceptions() as $uriPrefix) {
            static::assertInternalType('string', $uriPrefix);

            /**
            * this is here just for vimeo/psalm.
            *
            * @var string $uriPrefix
            */
            $uriPrefix = $uriPrefix;

            static::assertSame(
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
        /**
        * @var string[] $middlewares
        */
        $middlewares = [];
        $compiler = Fixtures\Compiler::ObtainCompiler();

        /**
        * @var string $middleware
        */
        foreach (static::YieldMiddlewareFromSource($className) as $middleware) {
            $middlewares[] = $middleware;
            $compiler->AddMiddleware($middleware);
        }

        static::assertSame($middlewares, $compiler->ObtainMiddleware());
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

        /**
        * @var string $route
        */
        foreach (static::YieldRoutesFromSource($className) as $route) {
            $routes[] = $route;
        }
        /**
        * @var string $middleware
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

        /**
        * this is here just for vimeo/psalm.
        *
        * @var array|false $present
        */
        $present = $dispatcher->dispatch($presentWithMethod, $presentWithUri);

        /**
        * this is here just for vimeo/psalm.
        *
        * @var array|false $notPresent
        */
        $notPresent = $dispatcher->dispatch(
            $notPresentWithMethod,
            $notPresentWithUri
        );

        static::assertInternalType('array', $present); // this is here just for vimeo/psalm
        static::assertInternalType('array', $notPresent); // this is here just for vimeo/psalm

        /**
        * this is here just for vimeo/psalm.
        *
        * @var array $present
        */
        $present = $present;

        /**
        * this is here just for vimeo/psalm.
        *
        * @var array $notPresent
        */
        $notPresent = $notPresent;

        static::assertTrue(Dispatcher::FOUND === $present[0]);
        static::assertTrue(Dispatcher::FOUND === $notPresent[0]);

        /**
        * @var string[] $dispatchedPresent
        */
        $dispatchedPresent = $present[1];

        /**
        * @var string[] $dispatchedNotPresent
        */
        $dispatchedNotPresent = $notPresent[1];

        static::assertSame(
            [
                $middleware,
                $presentWith,
            ],
            $dispatchedPresent
        );
        static::assertSame(
            [
                $notPresentWith,
            ],
            $dispatchedNotPresent
        );

        /**
        * @var string|false $route
        */
        $route = array_pop($dispatchedPresent);

        static::assertInternalType(
            'string',
            $route,
            'Last entry from a dispatcher should be a string'
        );

        /**
        * this bit is here just for vimeo/psalm.
        *
        * @var string $route
        */
        $route = $route;

        static::assertTrue(is_a($route, DaftRoute::class, true), sprintf(
            'Last entry from a dispatcher should be %s',
            DaftRoute::class
        ));

        if (is_array($dispatchedPresent) && count($dispatchedPresent) > 0) {
            foreach ($dispatchedPresent as $middleware) {
                static::assertTrue(is_a($middleware, DaftRouteFilter::class, true), sprintf(
                    'Leading entries from a dispatcher should be %s',
                    DaftRouteFilter::class
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
    *
    * @param string[] $sources
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
        * @var Dispatcher $dispatcher
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
        * @var null $content
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
            * @var string|resource $content
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
        * @var mixed[] $args
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
                'https://example.com/'
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
            * @var string $otherSource
            */
            foreach ($source::DaftRouterRouteAndMiddlewareSources() as $otherSource) {
                yield from static::YieldRoutesFromSource($otherSource);
            }
        }
    }

    protected static function YieldMiddlewareFromSource(string $source) : Generator
    {
        if (is_a($source, DaftRouteFilter::class, true)) {
            yield $source;
        }
        if (is_a($source, DaftSource::class, true)) {
            /**
            * @var string $otherSource
            */
            foreach ($source::DaftRouterRouteAndMiddlewareSources() as $otherSource) {
                yield from static::YieldMiddlewareFromSource($otherSource);
            }
        }
    }
}
