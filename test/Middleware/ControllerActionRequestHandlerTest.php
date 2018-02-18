<?php declare(strict_types=1);

namespace Cspray\ArchDemo\Test\Middleware;

use Auryn\Injector;
use Cspray\ArchDemo\Middleware\ControllerActionRequestHandler;
use Cspray\ArchDemo\Router\ControllerAction;
use Cspray\ArchDemo\Router\ResolvedRoute;
use Cspray\ArchDemo\Router\Router;
use Cspray\ArchDemo\Test\Stub\ControllerStub;
use PHPUnit\Framework\TestCase;
use Zend\Diactoros\ServerRequest;

class ControllerActionRequestHandlerTest extends TestCase {

    public function testControllerActionBothValid() {
        $request = new ServerRequest();
        $controllerAction = new ControllerAction(ControllerStub::class, 'action');
        $injector = $this->getMockBuilder(Injector::class)->disableOriginalConstructor()->getMock();
        $controller = new ControllerStub();
        $injector->expects($this->once())->method('make')->with(ControllerStub::class)->willReturn($controller);

        $router = $this->getMockBuilder(Router::class)->getMock();
        $resolvedRoute = new ResolvedRoute($request, $controllerAction, 200);
        $router->expects($this->once())->method('match')->with($request)->willReturn($resolvedRoute);

        $response = (new ControllerActionRequestHandler($router, $injector))->handle($request);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('From ControllerStub', (string) $response->getBody());
        $this->assertSame($request, $controller->getRequest());
    }

}