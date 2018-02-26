<?php

/**
 *
 * @license See LICENSE in source root
 * @version 1.0
 * @since   1.0
 */

namespace Cspray\ArchDemo\Test\Router\FastRoute;

use Cspray\ArchDemo\Controller\MethodNotAllowedController;
use Cspray\ArchDemo\Controller\NotFoundController;
use Cspray\ArchDemo\HttpStatusCodes;
use Cspray\ArchDemo\Router\ControllerAction;
use Cspray\ArchDemo\Router\FastRoute\Router as FastRouteRouter;
use Cspray\ArchDemo\Router\ResolvedRoute;
use Cspray\ArchDemo\Router\Route;
use Cspray\ArchDemo\Exception\InvalidArgumentException;
use Cspray\ArchDemo\Exception\InvalidTypeException;
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


class RouterTest extends TestCase {

    private function getRouter() {
        return new FastRouteRouter(
            new RouteCollector(new StdRouteParser(), new GcbDataGenerator()),
            function($data) { return new GcbDispatcher($data); }
        );
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

        $router->addRoute('GET', '/foo', new ControllerAction('foo', 'bar'));
        $router->addRoute('PUT', '/foo', new ControllerAction('foo', 'baz'));

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

        $router->addRoute('GET', '/foo', new ControllerAction('controller', 'action'));

        $resolved = $router->match($request);
        $this->assertInstanceOf(ResolvedRoute::class, $resolved);
        $this->assertTrue($resolved->isOk());
        $controllerAction = $resolved->getControllerAction();

        $this->assertSame('controller', $controllerAction->getController());
        $this->assertSame('action', $controllerAction->getAction());
    }

    public function testRouteWithParametersSetOnRequestAttributes() {
        $router = $this->getRouter();

        $router->addRoute('POST', '/foo/{name}/{id}', new ControllerAction('attr', 'action'));

        /** @var ServerRequestInterface $request */

        $request = (new ServerRequest())->withMethod('POST')
                                  ->withUri(new Uri('http://www.sprog.dev/foo/bar/qux'));

        $resolved = $router->match($request);

        $this->assertSame('bar', $resolved->getRequest()->getAttribute('name'));
        $this->assertSame('qux', $resolved->getRequest()->getAttribute('id'));
    }

    public function testArchDemoMetaRequestDataSetOnRequestAttributes() {
        $router = $this->getRouter();

        $router->addRoute('POST', '/foo', new ControllerAction('controller', 'action'));

        $request = (new ServerRequest())->withMethod('POST')
                                  ->withUri(new Uri('http://labrador.dev/foo'));

        $resolved = $router->match($request);

        $this->assertSame(['handler' => 'controller#action'], $resolved->getRequest()->getAttribute('_arch_demo'));
    }

    public function testGetRoutesWithJustOne() {
        $router = $this->getRouter();
        $router->addRoute('GET', '/foo', new ControllerAction('controller', 'action'));

        $routes = $router->getRoutes();
        $this->assertCount(1, $routes);
        $this->assertInstanceOf(Route::class, $routes[0]);
        $this->assertSame('/foo', $routes[0]->getPattern());
        $this->assertSame('GET', $routes[0]->getMethod());
        $this->assertSame('controller#action', (string) $routes[0]->getControllerAction());
    }

    public function testGetRoutesWithOnePatternSupportingMultipleMethods() {
        $router = $this->getRouter();
        $router->addRoute('GET', '/foo/bar', new ControllerAction('foo_bar', 'get'));
        $router->addRoute('POST', '/foo/bar', new ControllerAction('foo_bar', 'post'));
        $router->addRoute('PUT', '/foo/bar', new ControllerAction('foo_bar', 'put'));
        $router->addRoute('GET', '/foo/bar/{id}', new ControllerAction('foo_bar', 'id'));
        $router->addRoute('GET', '/foo/baz/{name}', new ControllerAction('foo_baz', 'name'));

        $expected = [
            ['GET', '/foo/bar', 'foo_bar#get'],
            ['POST', '/foo/bar', 'foo_bar#post'],
            ['PUT', '/foo/bar', 'foo_bar#put'],
            ['GET', '/foo/bar/{id}', 'foo_bar#id'],
            ['GET', '/foo/baz/{name}', 'foo_baz#name']
        ];
        $actual = [];
        $routes = $router->getRoutes();
        foreach ($routes as $route) {
            $this->assertInstanceOf(Route::class, $route);
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

    public function testUrlDecodingCustomAttributes() {
        $request = (new ServerRequest())->withMethod('GET')
            ->withUri(new Uri('http://example.com/foo%20bar'));
        $router = $this->getRouter();
        $router->addRoute('GET', '/{param}', new ControllerAction('something', 'action'));

        $resolved = $router->match($request);

        $this->assertTrue($resolved->isOk());
        $this->assertSame('foo bar', $resolved->getRequest()->getAttribute('param'));
    }

}
