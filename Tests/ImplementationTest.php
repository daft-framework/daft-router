<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftRouter\Tests;

use DateTimeImmutable;
use Generator;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase as Base;
use RuntimeException;
use SignpostMarv\DaftRouter\DaftRequestInterceptor;
use SignpostMarv\DaftRouter\DaftResponseModifier;
use SignpostMarv\DaftRouter\DaftRoute;
use SignpostMarv\DaftRouter\DaftRouteAcceptsBothEmptyAndTypedArgs;
use SignpostMarv\DaftRouter\DaftRouteAcceptsEmptyArgs;
use SignpostMarv\DaftRouter\DaftRouteAcceptsOnlyEmptyArgs;
use SignpostMarv\DaftRouter\DaftRouteAcceptsOnlyTypedArgs;
use SignpostMarv\DaftRouter\DaftRouteAcceptsTypedArgs;
use SignpostMarv\DaftRouter\DaftRouteFilter;
use SignpostMarv\DaftRouter\DaftRouterAutoMethodCheckingTrait;
use SignpostMarv\DaftRouter\DaftSource;
use SignpostMarv\DaftRouter\ResponseException;
use SignpostMarv\DaftRouter\Router\Compiler;
use SignpostMarv\DaftRouter\Router\Dispatcher;
use SignpostMarv\DaftRouter\TypedArgs;
use Symfony\Component\HttpFoundation\Request;
use Throwable;

/**
* @template HTTP_METHOD as 'GET'|'POST'|'CONNECT'|'DELETE'|'HEAD'|'OPTIONS'|'PATCH'|'PURGE'|'PUT'|'TRACE'
* @template T as array<string, scalar|DateTimeImmutable|null>
* @template S as array<string, scalar|null>
*/
class ImplementationTest extends Base
{
	/**
	* @return Generator<int, array{0:class-string<DaftSource>}, mixed, void>
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
	* @return Generator<int, array{0:class-string<DaftRouteFilter>, 1:class-string<DaftRoute>, 2:HTTP_METHOD, 3:string, 4:class-string<DaftRoute>, 5:HTTP_METHOD, 6:string}, mixed, void>
	*/
	public function DataProviderMiddlewareWithExceptions() : Generator
	{
		/**
		* @var array<int, array{0:class-string<DaftRouteFilter>, 1:class-string<DaftRoute>, 2:HTTP_METHOD, 3:string, 4:class-string<DaftRoute>, 5:HTTP_METHOD, 6:string}>
		*/
		$args = [
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

		yield from $args;
	}

	/**
	* @return Generator<int, array{0:class-string<DaftRoute>}, mixed, void>
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
	* @return Generator<int, array{0:class-string<DaftRouteFilter>}, mixed, void>
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

			foreach (
				static::YieldMiddlewareFromSource($source) as $middleware
			) {
				yield [$middleware];
			}
		}
	}

	/**
	* @return Generator<int, array{0:class-string<DaftRouteAcceptsEmptyArgs>|class-string<DaftRouteAcceptsTypedArgs>, 1:array<string, string>, 2:array<string, mixed>, 3:HTTP_METHOD, 4:string, 5?:class-string<Throwable>, 6?:string}, mixed, void>
	*/
	public function DataProviderRoutesWithKnownArgs() : Generator
	{
		/**
		* @var array<int, array{0:class-string<DaftRouteAcceptsEmptyArgs>|class-string<DaftRouteAcceptsTypedArgs>, 1:array<string, string>, 2:array<string, mixed>, 3:HTTP_METHOD, 4:string, 5?:class-string<Throwable>, 6?:string}>
		*/
		$args = [
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
				Fixtures\Login::class,
				[
					'mode' => 'admin',
				],
				[
					'mode' => 'admin',
				],
				'POST',
				'/admin/login',
			],
		];

		yield from $args;
	}

	/**
	* @return Generator<int, array{0:array<int, class-string<DaftRouteAcceptsEmptyArgs>|class-string<DaftRouteAcceptsTypedArgs>>, 1:string, 2:int, 3:string, 4:string[], 5:array<string, scalar|array|object|null>}, mixed, void>
	*/
	public function DataProviderVerifyHandlerGood() : Generator
	{
		yield from $this->DataProviderVerifyHandler(true);
	}

	/**
	* @return Generator<int, array{0:array<int, class-string<DaftRouteAcceptsEmptyArgs>|class-string<DaftRouteAcceptsTypedArgs>>, 1:string, 2:int, 3:string, 4:string[], 5:array<string, scalar|array|object|null>}, mixed, void>
	*/
	public function DataProviderVerifyHandlerBad() : Generator
	{
		yield from $this->DataProviderVerifyHandler(false);
	}

	/**
	* @return array<int, array{0:string, 1:string, 2:string}>
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
	* @return array<int, array{0:mixed}>
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
	* @param class-string<DaftSource> $className
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
				* @var class-string
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
	* @param class-string<DaftRoute> $className
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

		$routes = array_filter($routes, 'is_array');

		static::assertCount(
			$initialCount,
			$routes,
			'DaftRoute::DaftRouterRoutes() must be of the form array<string, array>'
		);

		foreach ($routes as $routesToCheck) {
			$initialCount = count($routesToCheck);

			static::assertGreaterThan(
				0,
				$initialCount,
				'URIs must have at least one method!'
			);

			$routesToCheck = array_filter(
				$routesToCheck,
				'is_int',
				ARRAY_FILTER_USE_KEY
			);

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
	* @param class-string<DaftRouteAcceptsEmptyArgs>|class-string<DaftRouteAcceptsTypedArgs> $className
	* @param array<string, string> $args
	* @param HTTP_METHOD $method
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
		$typed_args_object = $className::DaftRouterHttpRouteArgsTyped(
			$args,
			$method
		);

		if (is_null($typed_args_object)) {
			static::assertCount(0, $args);
		} else {
			static::assertSame($args, $typed_args_object->__toArray());

			/**
			* @var class-string<TypedArgs>
			*/
			$type = get_class($typed_args_object);
			static::assertSame($args, (new $type($typedArgs))->__toArray());
		}

		static::assertTrue(in_array(
			$className::DaftRouterHttpRouteDefaultMethod(),
			[
				'GET',
				'POST',
				'CONNECT',
				'DELETE',
				'HEAD',
				'OPTIONS',
				'PATCH',
				'PURGE',
				'PUT',
				'TRACE',
			],
			true
		));

		$check_auto_method_checking = (
			in_array(
				DaftRouterAutoMethodCheckingTrait::class,
				class_uses($className),
				true
			) ||
			is_a(
				$className,
				DaftRouteAcceptsBothEmptyAndTypedArgs::class,
				true
			) ||
			is_a($className, DaftRouteAcceptsOnlyEmptyArgs::class, true) ||
			is_a($className, DaftRouteAcceptsOnlyTypedArgs::class, true)
		);

		if ($typed_args_object instanceof TypedArgs) {
			if ( ! is_a($className, DaftRouteAcceptsTypedArgs::class, true)) {
				throw new RuntimeException(
					'Argument 2 passed to ' .
					__METHOD__ .
					'() resolved to an instance of ' .
					TypedArgs::class .
					', but ' .
					$className .
					' does not implement ' .
					DaftRouteAcceptsTypedArgs::class
				);
			}

			static::assertSame(
				$expectedRouteResult,
				$className::DaftRouterHttpRouteWithTypedArgs(
					$typed_args_object,
					$method
				)
			);

			if ($check_auto_method_checking) {
				static::expectException(InvalidArgumentException::class);
				static::expectExceptionMessage(
					'Specified method not supported!'
				);

				$className::DaftRouterHttpRouteWithTypedArgs(
					$typed_args_object,
					strrev($method)
				);
			}
		} elseif (
			! is_a($className, DaftRouteAcceptsEmptyArgs::class, true)
		) {
			throw new RuntimeException(
				'Argument 2 passed to ' .
				__METHOD__ .
				'() resolved to null, but ' .
				$className .
				' does not implement ' .
				DaftRouteAcceptsEmptyArgs::class
			);
		} else {
			static::assertSame(
				$expectedRouteResult,
				$className::DaftRouterHttpRouteWithEmptyArgs($method)
			);

			if ($check_auto_method_checking) {
				static::expectException(InvalidArgumentException::class);
				static::expectExceptionMessage(
					'Specified method not supported!'
				);

				$className::DaftRouterHttpRouteWithEmptyArgs(strrev($method));
			}
		}
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
	public function testCompilerVerifyAddRouteAddsRoutes(
		string $className
	) : void {
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
	* @param class-string<DaftRouteFilter> $className
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

		$uriPrefixes = $className::DaftRouterRoutePrefixExceptions();

		$initialCount = count($uriPrefixes);

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
	* @param class-string<DaftRequestInterceptor>|class-string<DaftResponseModifier>|class-string<DaftSource> $className
	*
	* @depends testCompilerVerifyAddMiddlewareThrowsException
	*
	* @dataProvider DataProviderGoodSources
	*/
	public function testCompilerVerifyAddMiddlewareAddsMiddlewares(
		string $className
	) : void {
		/**
		* @var string[]
		*/
		$middlewares = [];
		$compiler = Fixtures\Compiler::ObtainCompiler();

		foreach (
			static::YieldMiddlewareFromSource($className) as $middleware
		) {
			$middlewares[] = $middleware;
			$compiler->AddMiddleware($middleware);
		}

		$middlewares[] = DaftRouteFilter::class;
		$middlewares = array_filter(
			$middlewares,
			function (string $middleware) : bool {
				return
					is_a($middleware, DaftRequestInterceptor::class, true) ||
					is_a($middleware, DaftResponseModifier::class, true);
			}
		);

		static::assertSame($middlewares, $compiler->ObtainMiddleware());
	}

	/**
	* @param class-string<DaftSource> $className
	*
	* @depends testCompilerVerifyAddRouteAddsRoutes
	* @depends testCompilerVerifyAddMiddlewareAddsMiddlewares
	*
	* @dataProvider DataProviderGoodSources
	*/
	public function testCompilerDoesNotDuplicateConfigEntries(
		string $className
	) : void {
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
		foreach (
			static::YieldMiddlewareFromSource($className) as $middleware
		) {
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
	* @param class-string<DaftRouteFilter> $middleware
	* @param class-string<DaftRoute> $presentWith
	* @param HTTP_METHOD $presentWithMethod
	* @param class-string<DaftRoute> $notPresentWith
	* @param HTTP_METHOD $notPresentWithMethod
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
			$expectedWithMiddleware[
				DaftRequestInterceptor::class
			][] = $middleware;
		}

		if (is_a($middleware, DaftResponseModifier::class, true)) {
			$expectedWithMiddleware[
				DaftResponseModifier::class
			][] = $middleware;
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

		$route = array_pop($dispatchedPresent);

		/**
		* @var array
		*/
		$dispatchedPresent = $dispatchedPresent;

		static::assertIsString(
			$route,
			'Last entry from a dispatcher should be a string'
		);

		static::assertTrue(is_a($route, DaftRoute::class, true), sprintf(
			'Last entry from a dispatcher should be %s',
			DaftRoute::class
		));

		static::assertCount(2, $dispatchedPresent);
		static::assertTrue(isset(
			$dispatchedPresent[DaftRequestInterceptor::class]
		));
		static::assertTrue(isset(
			$dispatchedPresent[DaftResponseModifier::class]
		));
		static::assertIsArray(
			$dispatchedPresent[DaftRequestInterceptor::class]
		);
		static::assertIsArray($dispatchedPresent[DaftResponseModifier::class]);

		$interceptors = $dispatchedPresent[DaftRequestInterceptor::class];

		$modifiers = $dispatchedPresent[DaftResponseModifier::class];

		$initialCount = count($interceptors);

		$interceptors = array_filter($interceptors, 'is_string');

		static::assertCount($initialCount, $interceptors);
		static::assertSame(array_values($interceptors), $interceptors);

		/**
		* @var array<int, string>
		*/
		$interceptors = array_values($interceptors);

		$initialCount = count($modifiers);

		$modifiers = array_filter($modifiers, 'is_string');

		static::assertCount($initialCount, $modifiers);
		static::assertSame(array_values($modifiers), $modifiers);

		/**
		* @var array<int, string>
		*/
		$modifiers = array_values($modifiers);

		foreach ($interceptors as $interceptor) {
			static::assertTrue(
				is_a(
					$interceptor,
					DaftRequestInterceptor::class,
					true
				),
				sprintf(
					'Leading entries from a dispatcher should be %s',
					DaftRequestInterceptor::class
				)
			);
		}

		foreach ($modifiers as $modifier) {
			static::assertTrue(
				is_a(
					$modifier,
					DaftResponseModifier::class,
					true
				),
				sprintf(
					'Leading entries from a dispatcher should be %s',
					DaftResponseModifier::class
				)
			);
		}
	}

	/**
	* @depends testCompilerVerifyAddRouteAddsRoutes
	* @depends testCompilerVerifyAddMiddlewareAddsMiddlewares
	* @depends testCompilerExcludesMiddleware
	*
	* @dataProvider DataProviderVerifyHandlerGood
	*
	* @param array<int, class-string<DaftRouteAcceptsEmptyArgs>|class-string<DaftRouteAcceptsTypedArgs>> $sources
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
		$dispatcher = Fixtures\Compiler::ObtainCompiler()::ObtainDispatcher(
			[
				'cacheDisabled' => true,
				'cacheFile' => tempnam(sys_get_temp_dir(), static::class),
				'dispatcher' => Dispatcher::class,
			],
			...$sources
		);

		$request = static::RequestFromArgs($requestArgs);

		$response = $dispatcher->handle($request, $prefix);

		static::assertSame($expectedStatus, $response->getStatusCode());
		static::assertSame($expectedContent, $response->getContent());

		foreach ($expectedHeaders as $header => $value) {
			static::assertSame($response->headers->get($header), $value);
		}
	}

	/**
	* @depends testHandlerGood
	*
	* @dataProvider DataProviderVerifyHandlerGood
	*
	* @param array<int, class-string<DaftRouteAcceptsEmptyArgs>|class-string<DaftRouteAcceptsTypedArgs>> $sources
	* @param array<string, scalar|array|object|null> $expectedHeaders
	*/
	public function testHandlerGoodWithFixturesDispatcher(
		array $sources,
		string $prefix,
		int $expectedStatus,
		string $expectedContent,
		array $requestArgs,
		array $expectedHeaders = []
	) : void {
		$dispatcher = Fixtures\CompilerWithFixturesDispatcher::ObtainCompiler(
		)::ObtainDispatcher(
			[
				'cacheDisabled' => true,
				'cacheFile' => tempnam(sys_get_temp_dir(), static::class),
				'dispatcher' => Fixtures\Dispatcher::class,
			],
			...$sources
		);

		$request = static::RequestFromArgs($requestArgs);

		$response = $dispatcher->handle($request, $prefix);

		static::assertSame($expectedStatus, $response->getStatusCode());
		static::assertSame($expectedContent, $response->getContent());

		foreach ($expectedHeaders as $header => $value) {
			static::assertSame($response->headers->get($header), $value);
		}
	}

	/**
	* @depends testHandlerGoodWithFixturesDispatcher
	*
	* @param array<int, class-string<DaftRouteAcceptsEmptyArgs>|class-string<DaftRouteAcceptsTypedArgs>> $sources
	*/
	public function testHandlerUntypedRequestHandlingIsDeprecated() : void
	{
		/**
		* @var Fixtures\Dispatcher
		*/
		$dispatcher = Fixtures\CompilerWithFixturesDispatcher::ObtainCompiler(
		)::ObtainDispatcher(
			[
				'cacheDisabled' => true,
				'cacheFile' => tempnam(sys_get_temp_dir(), static::class),
				'dispatcher' => Fixtures\Dispatcher::class,
			],
		);

		static::expectException(RuntimeException::class);
		static::expectExceptionMessage(
			'Untyped request handling is deprecated!'
		);

		$dispatcher->handleRouteInfoResponseParentPublic(
			static::RequestFromArgs(['https://example.com/']),
			Fixtures\Home::class,
			Fixtures\LocatorArgs::__fromArray(['locator' => 'foo']),
			[],
			[]
		);
	}

	/**
	* @depends testCompilerVerifyAddRouteAddsRoutes
	* @depends testCompilerVerifyAddMiddlewareAddsMiddlewares
	* @depends testCompilerExcludesMiddleware
	*
	* @dataProvider DataProviderVerifyHandlerBad
	*
	* @param array<int, class-string<DaftRouteAcceptsEmptyArgs>|class-string<DaftRouteAcceptsTypedArgs>> $sources
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

		$request = static::RequestFromArgs($requestArgs);

		$this->expectException(ResponseException::class);
		$this->expectExceptionCode($expectedStatus);
		$this->expectExceptionMessage($expectedContent);

		$dispatcher->handle($request, $prefix);
	}

	/**
	* @return Generator<int, array{0:class-string<TypedArgs>|null, 1:array<string, scalar|null>|array<empty, empty>, 2:string, 3:array<string, scalar|null>}, mixed, void>
	*/
	public function dataProviderJsonSerialize() : Generator
	{
		yield from [
			[
				Fixtures\AdminModeArgs::class,
				[
					'mode' => 'admin',
				],
				'{"mode":"admin"}',
				[
					'mode' => 'admin',
				],
			],
			[
				Fixtures\DateArgs::class,
				[
					'a' => 'January 1st, 1970',
					'b' => 'January 1st, 1970',
				],
				'{"a":"1970-01-01","b":"1970W01"}',
				[
					'a' => '1970-01-01',
					'b' => '1970W01',
				],
			],
			[
				null,
				[],
				'{}',
				[],
			],
		];
	}

	/**
	* @dataProvider dataProviderJsonSerialize
	*
	* @template K as key-of<T>
	*
	* @param class-string<TypedArgs>|null $type
	* @param array<string, scalar|null> $args
	* @param array<string, scalar|null> $expected_decoded
	*/
	public function testJsonSerialize(
		? string $type,
		array $args,
		string $expected,
		array $expected_decoded
	) : void {
		if (is_null($type)) {
			$typed_args = null;
			$encoded = '{}';
			$for_json = [];
		} else {
			$typed_args = $type::__fromArray($args);

			$for_json = $typed_args->jsonSerialize();
			$encoded = json_encode($typed_args, JSON_FORCE_OBJECT);
		}

		/**
		* @var array<string, scalar|null>
		*/
		$decoded = json_decode($encoded, true);

		static::assertSame($expected_decoded, $decoded);

		static::assertSame($expected, $encoded);

		if ($typed_args instanceof TypedArgs) {
			foreach ($decoded as $property => $decoded_value) {
				static::assertTrue(isset($args[$property]));
				static::assertSame(
					$decoded_value,
					$for_json[$property]
				);

				/**
				* @var T[K]
				*/
				$typed_value = $typed_args->$property;

				static::assertSame(
					$decoded[$property],
					$typed_args::PropertyValueToScalarOrNull(
						$property,
						$typed_value
					)
				);
			}
		}
	}

	protected static function RequestFromArgs(array $requestArgs) : Request
	{
		$uri = (string) $requestArgs[0];
		$method = 'GET';
		$parameters = [];
		$cookies = [];
		$files = [];
		$server = [];

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
	* @return Generator<int, array{0:array<int, class-string<DaftRouteAcceptsEmptyArgs>|class-string<DaftRouteAcceptsTypedArgs>>, 1:string, 2:int, 3:string, 4:string[], 5:array<string, scalar|array|object|null>}, mixed, void>
	*/
	protected function DataProviderVerifyHandler(bool $good = true) : Generator
	{
		$argsSource = $good ? $this->DataProviderGoodHandler() : $this->DataProviderBadHandler();
		/**
		* @var mixed[]
		*/
		foreach ($argsSource as $args) {
			[
				$sources,
				$prefix,
				$expectedStatus,
				$expectedContent,
				$headers,
				$uri,
			] = $args;

			/**
			* @var array{0:array<int, class-string<DaftRouteAcceptsEmptyArgs>|class-string<DaftRouteAcceptsTypedArgs>>, 1:string, 2:int, 3:string, 4:string[], 5:array<string, scalar|array|object|null>}
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
			[
				[
					Fixtures\ConfigNoModify::class,
				],
				'',
				200,
				'',
				[],
				'https://example.com/admin/login?loggedin',
			],
			[
				[
					Fixtures\ConfigNoModify::class,
				],
				'',
				200,
				'',
				[],
				'https://example.com/login?loggedin',
			],
			[
				[
					Fixtures\ConfigNoModify::class,
				],
				'',
				200,
				'',
				[],
				'https://example.com/profile/1~foo?loggedin',
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
	* @return Generator<int, class-string<DaftRoute>, mixed, void>
	*/
	protected static function YieldRoutesFromSource(string $source) : Generator
	{
		if (is_a($source, DaftRoute::class, true)) {
			yield $source;
		}

		if (is_a($source, DaftSource::class, true)) {
			foreach (
				$source::DaftRouterRouteAndMiddlewareSources() as $otherSource
			) {
				yield from static::YieldRoutesFromSource($otherSource);
			}
		}
	}

	/**
	* @param class-string<DaftRequestInterceptor>|class-string<DaftResponseModifier>|class-string<DaftSource> $source
	*
	* @return Generator<int, class-string<DaftRequestInterceptor>|class-string<DaftResponseModifier>, mixed, void>
	*/
	protected static function YieldMiddlewareFromSource(
		string $source
	) : Generator {
		if (
			is_a($source, DaftRequestInterceptor::class, true) ||
			is_a($source, DaftResponseModifier::class, true)
		) {
			yield $source;
		}

		if (is_a($source, DaftSource::class, true)) {
			foreach (
				$source::DaftRouterRouteAndMiddlewareSources() as $otherSource
			) {
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
