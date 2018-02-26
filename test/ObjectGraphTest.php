<?php declare(strict_types=1);

namespace Cspray\ArchDemo\Test;

use Auryn\Injector;
use Cspray\ArchDemo\Config\CorsConfig;
use Cspray\ArchDemo\Config\DatabaseConfig;
use Cspray\ArchDemo\Config\Environment;
use Cspray\ArchDemo\Middleware\ControllerActionRequestHandler;
use Cspray\ArchDemo\ObjectGraph;
use Cspray\ArchDemo\Router\Router;
use Cspray\ArchDemo\Router\FastRoute\Router as FastRouteRouter;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use League\Fractal\Manager;
use PHPUnit\Framework\TestCase;

class ObjectGraphTest extends TestCase
{

    public function testInjectorShared()
    {
        $environment = Environment::loadFromArray('development', ['db' => ['driver' => 'pdo_sqlite']]);
        $injector = (new ObjectGraph($environment))->createContainer();

        $injector2 = $injector->make(Injector::class);

        $this->assertSame($injector, $injector2);
    }

    public function testMakingRouterReturnsFastRouteRouter()
    {
        $environment = Environment::loadFromArray('development', ['db' => ['driver' => 'pdo_sqlite']]);
        $injector = (new ObjectGraph($environment))->createContainer();

        $router = $injector->make(Router::class);
        $this->assertInstanceOf(FastRouteRouter::class, $router);
    }

    public function testMakingRouterSharesSameObject()
    {
        $environment = Environment::loadFromArray('development', ['db' => ['driver' => 'pdo_sqlite']]);
        $injector = (new ObjectGraph($environment))->createContainer();

        $router1 = $injector->make(Router::class);
        $router2 = $injector->make(Router::class);

        $this->assertSame($router1, $router2);
    }

    public function testMakingEntityManagerInterfaceReturnsImplementation()
    {
        $environment = Environment::loadFromArray('development', ['db' => ['driver' => 'pdo_sqlite']]);
        $injector = (new ObjectGraph($environment))->createContainer();

        $manager = $injector->make(EntityManagerInterface::class);
        $this->assertInstanceOf(EntityManager::class, $manager);
    }

    public function testMakingEntityManagerSharesSameObject()
    {
        $environment = Environment::loadFromArray('development', ['db' => ['driver' => 'pdo_sqlite']]);
        $injector = (new ObjectGraph($environment))->createContainer();

        $manager1 = $injector->make(EntityManagerInterface::class);
        $manager2 = $injector->make(EntityManagerInterface::class);

        $this->assertSame($manager1, $manager2);
    }

    public function testMakingFractalManagerSharesSameObject()
    {
        $environment = Environment::loadFromArray('development', ['db' => ['driver' => 'pdo_sqlite']]);
        $injector = (new ObjectGraph($environment))->createContainer();

        $manager1 = $injector->make(Manager::class);
        $manager2 = $injector->make(Manager::class);

        $this->assertSame($manager1, $manager2);
    }

    public function testMakingControllerActionMiddlewareSharesSameObject()
    {
        $environment = Environment::loadFromArray('development', ['db' => ['driver' => 'pdo_sqlite']]);
        $injector = (new ObjectGraph($environment))->createContainer();

        $handler1 = $injector->make(ControllerActionRequestHandler::class);
        $handler2 = $injector->make(ControllerActionRequestHandler::class);

        $this->assertSame($handler1, $handler2);
    }

    public function testMakingEnvironmentConfigSharesObject()
    {
        $environment = Environment::loadFromArray('development', ['db' => ['driver' => 'pdo_sqlite']]);
        $injector = (new ObjectGraph($environment))->createContainer();

        $env1 = $injector->make(Environment::class);
        $env2 = $injector->make(Environment::class);

        $this->assertSame($env1, $env2);
    }

    public function testMakingCorsConfigSameFromEnvironmentConfig()
    {
        $environment = Environment::loadFromArray('development', ['db' => ['driver' => 'pdo_sqlite']]);
        $injector = (new ObjectGraph($environment))->createContainer();

        $cors = $injector->make(CorsConfig::class);

        $this->assertSame($environment->corsConfig(), $cors);
    }


    public function testMakingDatabaseConfigSameFromEnvironmentConfig()
    {
        $environment = Environment::loadFromArray('development', ['db' => ['driver' => 'pdo_sqlite']]);
        $injector = (new ObjectGraph($environment))->createContainer();

        $cors = $injector->make(DatabaseConfig::class);

        $this->assertSame($environment->databaseConfig(), $cors);
    }
}
