<?php declare(strict_types=1);

namespace Cspray\ArchDemo\Test;

use Auryn\Injector;
use Cspray\ArchDemo\Config\Environment;
use Cspray\ArchDemo\Middleware\ControllerActionRequestHandler;
use Cspray\ArchDemo\ObjectGraph;
use Cspray\ArchDemo\Router\FastRouteRouter;
use Cspray\ArchDemo\Router\Router;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use League\Fractal\Manager;
use PHPUnit\Framework\TestCase;

class ObjectGraphTest extends TestCase {

    public function testInjectorShared() {
        $environment = Environment::loadFromArray('development', ['db.driver' => 'pdo_sqlite']);
        $injector = (new ObjectGraph($environment))->createContainer();

        $injector2 = $injector->make(Injector::class);

        $this->assertSame($injector, $injector2);
    }

    public function testMakingRouterReturnsFastRouteRouter() {
        $environment = Environment::loadFromArray('development', ['db.driver' => 'pdo_sqlite']);
        $injector = (new ObjectGraph($environment))->createContainer();

        $router = $injector->make(Router::class);
        $this->assertInstanceOf(FastRouteRouter::class, $router);
    }

    public function testMakingRouterSharesSameObject() {
        $environment = Environment::loadFromArray('development', ['db.driver' => 'pdo_sqlite']);
        $injector = (new ObjectGraph($environment))->createContainer();

        $router1 = $injector->make(Router::class);
        $router2 = $injector->make(Router::class);

        $this->assertSame($router1, $router2);
    }

    public function testMakingEntityManagerInterfaceReturnsImplementation() {
        $environment = Environment::loadFromArray('development', ['db.driver' => 'pdo_sqlite']);
        $injector = (new ObjectGraph($environment))->createContainer();

        $manager = $injector->make(EntityManagerInterface::class);
        $this->assertInstanceOf(EntityManager::class, $manager);
    }

    public function testMakingEntityManagerSharesSameObject() {
        $environment = Environment::loadFromArray('development', ['db.driver' => 'pdo_sqlite']);
        $injector = (new ObjectGraph($environment))->createContainer();

        $manager1 = $injector->make(EntityManagerInterface::class);
        $manager2 = $injector->make(EntityManagerInterface::class);

        $this->assertSame($manager1, $manager2);
    }

    public function testMakingFractalManagerSharesSameObject() {
        $environment = Environment::loadFromArray('development', ['db.driver' => 'pdo_sqlite']);
        $injector = (new ObjectGraph($environment))->createContainer();

        $manager1 = $injector->make(Manager::class);
        $manager2 = $injector->make(Manager::class);

        $this->assertSame($manager1, $manager2);
    }

    public function testMakingControllerActionMiddlewareSharesSameObject() {
        $environment = Environment::loadFromArray('development', ['db.driver' => 'pdo_sqlite']);
        $injector = (new ObjectGraph($environment))->createContainer();

        $handler1 = $injector->make(ControllerActionRequestHandler::class);
        $handler2 = $injector->make(ControllerActionRequestHandler::class);

        $this->assertSame($handler1, $handler2);
    }


}