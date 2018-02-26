<?php declare(strict_types=1);

namespace Cspray\ArchDemo\Test\Router;

use Cspray\ArchDemo\HttpStatusCodes;
use Cspray\ArchDemo\Router\ControllerAction;
use Cspray\ArchDemo\Router\FriendlyRouter;
use Cspray\ArchDemo\Exception\InvalidArgumentException;
use Cspray\ArchDemo\Router\ResolvedRoute;
use Cspray\ArchDemo\Router\Router;
use Cspray\ArchDemo\Test\Stub\RouterStub;
use PHPUnit\Framework\TestCase;
use Zend\Diactoros\ServerRequest;

class FriendlyRouterTest extends TestCase {

    private function getRouter() : FriendlyRouter {
        return new FriendlyRouter(new RouterStub());
    }

    public function testCallingMatchDelegatesToConstructorDependency() {
        $router = $this->getMockBuilder(Router::class)->getMock();
        $subject = new FriendlyRouter($router);
        $request = new ServerRequest();
        $resolved = new ResolvedRoute($request, new ControllerAction('foo', 'bar'), HttpStatusCodes::CREATED);
        $router->expects($this->once())->method('match')->with($request)->willReturn($resolved);

        $actual = $subject->match($request);
        $this->assertSame($resolved, $actual);
    }

    public function testPassingInvalidControllerActionThrowsException() {
        $router = $this->getRouter();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('An invalid route controller#action was passed, got bad_news');
        $router->get('/', 'bad_news');
    }

    public function testMountingRouterAddsPrefix() {
        $router = $this->getRouter();
        $router->mount('/prefix', function(FriendlyRouter $router) {
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
        $router->mount('/foo', function(FriendlyRouter $router) {
            $router->delete('/foo-get', 'one#action');
            $router->mount('/bar', function(FriendlyRouter $router) {
                $router->post('/bar-post', 'two#action');
                $router->mount('/baz', function(FriendlyRouter $router) {
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

    public function testSettingMountedRoot() {
        $router = $this->getRouter();
        $router->mount('/foo', function(FriendlyRouter $router) {
            $router->get($router->root(), 'something#action');
        });

        $expected = [
            ['GET', '/foo', 'something#action']
        ];
        $actual = [];
        foreach ($router->getRoutes() as $route) {
            $actual[] = [$route->getMethod(), $route->getPattern(), (string) $route->getControllerAction()];
        }

        $this->assertSame($expected, $actual);
    }

    public function testUsingRouterRootWithoutMount() {
        $router = $this->getRouter();
        $router->get($router->root(), 'something#action');

        $expected = [
            ['GET', '/', 'something#action']
        ];
        $actual = [];
        foreach ($router->getRoutes() as $route) {
            $actual[] = [$route->getMethod(), $route->getPattern(), (string) $route->getControllerAction()];
        }

        $this->assertSame($expected, $actual);
    }

    public function testResourceAddsAppropriateRoutes() {
        $router = $this->getRouter();
        $router->resource('dogs', 'DogController');

        $expected = [
            ['GET', '/dogs', 'DogController#index'],
            ['GET', '/dogs/{id}', 'DogController#show'],
            ['POST', '/dogs', 'DogController#create'],
            ['PUT', '/dogs/{id}', 'DogController#update'],
            ['DELETE', '/dogs/{id}', 'DogController#delete']
        ];

        $actual = [];
        foreach ($router->getRoutes() as $route) {
            $actual[] = [$route->getMethod(), $route->getPattern(), (string) $route->getControllerAction()];
        }

        $this->assertSame($expected, $actual);
    }

}