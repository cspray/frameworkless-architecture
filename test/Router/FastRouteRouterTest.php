<?php

/**
 *
 * @license See LICENSE in source root
 * @version 1.0
 * @since   1.0
 */

namespace Cspray\ArchDemo\Test\Router;

use Cspray\ArchDemo\{
    Controller\MethodNotAllowedController as MethodNotAllowedController,
    Controller\NotFoundController as NotFoundController,
    HttpStatusCodes,
    Router\ControllerAction,
    Router\FastRouteRouter,
    Router\ResolvedRoute,
    Router\Route,
    Exception\InvalidArgumentException,
    Exception\InvalidTypeException
};
use FastRoute\{
    DataGenerator\GroupCountBased as GcbDataGenerator,
    Dispatcher\GroupCountBased as GcbDispatcher,
    RouteCollector,
    RouteParser\Std as StdRouteParser
};
use Psr\Http\Message\{
    ResponseInterface,
    ServerRequestInterface
};
use Zend\Diactoros\{
    ServerRequest,
    Uri
};
use PHPUnit\Framework\TestCase;


class FastRouteRouterTest extends TestCase {

    private $mockResolver;

    private function getRouter() {
        return new FastRouteRouter(
            new RouteCollector(new StdRouteParser(), new GcbDataGenerator()),
            function($data) { return new GcbDispatcher($data); }
        );
    }

    public function testPassingInvalidControllerActionThrowsException() {
        $router = $this->getRouter();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('An invalid route controller#action was passed, got bad_news');
        $router->get('/', 'bad_news');
    }

    public function testFastRouteDispatcherCallbackReturnsImproperTypeThrowsException() {
        $router = new FastRouteRouter(
            new RouteCollector(new StdRouteParser(), new GcbDataGenerator()),
            function() { return 'not a dispatcher'; }
        );

        $this->expectException(InvalidTypeException::class);
        $this->expectExceptionMessage('A FastRoute\\Dispatcher must be returned from dispatcher callback injected in constructor');

        $router->match(new ServerRequest());
    }

    public function testRouterNotFoundReturnsCorrectResolvedRoute() {
        $router = $this->getRouter();
        $resolved = $router->match(new ServerRequest());
        $this->assertInstanceOf(ResolvedRoute::class, $resolved);
        $this->assertTrue($resolved->isNotFound());
        $controllerAction = $resolved->getControllerAction();

        $this->assertSame(NotFoundController::class, $controllerAction->getController());
        $this->assertSame('index', $controllerAction->getAction());
    }

    public function testRouterMethodNotAllowedReturnsCorrectResolvedRoute() {
        $router = $this->getRouter();
        $request = (new ServerRequest())->withMethod('POST')
            ->withUri(new Uri('http://labrador.dev/foo'));
        $router->get('/foo', 'foo#bar');
        $router->put('/foo', 'foo#baz');

        $resolved = $router->match($request);
        $this->assertInstanceOf(ResolvedRoute::class, $resolved);
        $this->assertTrue($resolved->isMethodNotAllowed());
        $this->assertSame(['GET', 'PUT'], $resolved->getAvailableMethods());

        $controllerAction = $resolved->getControllerAction();

        $this->assertSame(MethodNotAllowedController::class, $controllerAction->getController());
        $this->assertSame('index', $controllerAction->getAction());
    }

    public function testRouterIsOkReturnsCorrectResolvedRoute() {
        $router = $this->getRouter();

        $request = (new ServerRequest())->withMethod('GET')
            ->withUri(new Uri('http://labrador.dev/foo'));
        $router->get('/foo', 'controller#action');

        $resolved = $router->match($request);
        $this->assertInstanceOf(ResolvedRoute::class, $resolved);
        $this->assertTrue($resolved->isOk());
        $controllerAction = $resolved->getControllerAction();

        $this->assertSame('controller', $controllerAction->getController());
        $this->assertSame('action', $controllerAction->getAction());
    }

    public function testRouteWithParametersSetOnRequestAttributes() {
        $router = $this->getRouter();

        $router->post('/foo/{name}/{id}', 'attr#action');

        /** @var ServerRequestInterface $request */

        $request = (new ServerRequest())->withMethod('POST')
                                  ->withUri(new Uri('http://www.sprog.dev/foo/bar/qux'));

        $resolved = $router->match($request);

        $this->assertSame('bar', $resolved->getRequest()->getAttribute('name'));
        $this->assertSame('qux', $resolved->getRequest()->getAttribute('id'));
    }

    public function testArchDemoMetaRequestDataSetOnRequestAttributes() {
        $router = $this->getRouter();

        $router->post('/foo', 'controller#action');

        $request = (new ServerRequest())->withMethod('POST')
                                  ->withUri(new Uri('http://labrador.dev/foo'));

        $resolved = $router->match($request);

        $this->assertSame(['handler' => 'controller#action'], $resolved->getRequest()->getAttribute('_arch_demo'));
    }

    public function testGetRoutesWithJustOne() {
        $router = $this->getRouter();
        $router->get('/foo', 'controller#action');

        $routes = $router->getRoutes();
        $this->assertCount(1, $routes);
        $this->assertInstanceOf(Route::class, $routes[0]);
        $this->assertSame('/foo', $routes[0]->getPattern());
        $this->assertSame('GET', $routes[0]->getMethod());
        $this->assertSame('controller#action', (string) $routes[0]->getControllerAction());
    }

    public function testGetRoutesWithOnePatternSupportingMultipleMethods() {
        $router = $this->getRouter();
        $router->get('/foo/bar', 'foo_bar#get');
        $router->post('/foo/bar', 'foo_bar#post');
        $router->put('/foo/bar', 'foo_bar#put');

        $expected = [
            ['GET', '/foo/bar', 'foo_bar#get'],
            ['POST', '/foo/bar', 'foo_bar#post'],
            ['PUT', '/foo/bar', 'foo_bar#put']
        ];
        $actual = [];
        $routes = $router->getRoutes();
        foreach ($routes as $route) {
            $this->assertInstanceOf(Route::class, $route);
            $actual[] = [$route->getMethod(), $route->getPattern(), (string) $route->getControllerAction()];
        }

        $this->assertSame($expected, $actual);
    }

    public function testGetRoutesWithStaticAndVariable() {
        $router = $this->getRouter();
        $router->get('/foo/bar/{id}', 'foo_bar#id');
        $router->get('/foo/baz/{name}', 'foo_baz#name');
        $router->post('/foo/baz', 'foo_baz#post');
        $router->put('/foo/quz', 'foo_quz#put');

        $expected = [
            ['GET', '/foo/bar/{id}', 'foo_bar#id'],
            ['GET', '/foo/baz/{name}', 'foo_baz#name'],
            ['POST', '/foo/baz', 'foo_baz#post'],
            ['PUT', '/foo/quz', 'foo_quz#put']
        ];
        $actual = [];
        $routes = $router->getRoutes();
        foreach ($routes as $route) {
            $this->assertInstanceOf(Route::class, $route);
            $actual[] = [$route->getMethod(), $route->getPattern(), (string) $route->getControllerAction()];
        }

        $this->assertSame($expected, $actual);
    }

    public function testMountingRouterAddsPrefix() {
        $router = $this->getRouter();
        $router->mount('/prefix', function(FastRouteRouter $router) {
            $router->get('/foo', 'something#action');
        });
        $router->get('/noprefix', 'something_else#action');

        $expected = [
            ['GET', '/prefix/foo', 'something#action'],
            ['GET', '/noprefix', 'something_else#action']
        ];
        $actual = [];
        $routes = $router->getRoutes();
        foreach ($routes as $route) {
            $actual[] = [$route->getMethod(), $route->getPattern(), (string) $route->getControllerAction()];
        }

        $this->assertSame($expected, $actual);
    }

    public function testNestedMountingAddsCorrectPrefixes() {
        $router = $this->getRouter();
        $router->mount('/foo', function(FastRouteRouter $router) {
            $router->delete('/foo-get', 'one#action');
            $router->mount('/bar', function(FastRouteRouter $router) {
                $router->post('/bar-post', 'two#action');
                $router->mount('/baz', function(FastRouteRouter $router) {
                    $router->put('/baz-put', 'three#action');
                });
            });
        });

        $expected = [
            ['DELETE', '/foo/foo-get', 'one#action'],
            ['POST', '/foo/bar/bar-post', 'two#action'],
            ['PUT', '/foo/bar/baz/baz-put', 'three#action']
        ];
        $actual = [];
        foreach ($router->getRoutes() as $route) {
            $actual[] = [$route->getMethod(), $route->getPattern(), (string) $route->getControllerAction()];
        }

        $this->assertSame($expected, $actual);
    }

    public function testSettingNotFoundController() {
        $router = $this->getRouter();
        $router->setNotFoundControllerAction(new ControllerAction("YourNotFoundController", "action"));
        $controllerAction = $router->getNotFoundControllerAction();
        $this->assertSame("YourNotFoundController", $controllerAction->getController());
        $this->assertSame("action", $controllerAction->getAction());
    }

    public function testSettingMethodNotAllowedController() {
        $router = $this->getRouter();
        $router->setMethodNotAllowedControllerAction(new ControllerAction("YourMethodNotAllowedController", "action"));
        $controllerAction = $router->getMethodNotAllowedControllerAction();
        $this->assertSame("YourMethodNotAllowedController", $controllerAction->getController());
        $this->assertSame("action", $controllerAction->getAction());
    }

    public function testSettingMountedRoot() {
        $router = $this->getRouter();
        $router->mount('/foo', function($router) {
            $router->get($router->root(), 'something#action');
        });

        $request = (new ServerRequest())->withMethod('GET')
                                  ->withUri(new Uri('http://example.com/foo'));

        $resolved = $router->match($request);
        $controllerAction= $resolved->getControllerAction();
        $this->assertSame('something', $controllerAction->getController());
        $this->assertSame('action', $controllerAction->getAction());
    }

    public function testUsingRouterRootWithoutMount() {
        $request = (new ServerRequest())->withMethod('GET')
            ->withUri(new Uri('http://example.com'));
        $router = $this->getRouter();
        $router->get($router->root(), 'something#action');

        $resolved = $router->match($request);
        $controllerAction = $resolved->getControllerAction();
        $this->assertSame('something', $controllerAction->getController());
        $this->assertSame('action', $controllerAction->getAction());
    }

    public function testUrlDecodingCustomAttributes() {
        $request = (new ServerRequest())->withMethod('GET')
            ->withUri(new Uri('http://example.com/foo%20bar'));
        $router = $this->getRouter();
        $router->get('/{param}', 'something#action');

        $resolved = $router->match($request);

        $this->assertTrue($resolved->isOk());
        $this->assertSame('foo bar', $resolved->getRequest()->getAttribute('param'));
    }

}
