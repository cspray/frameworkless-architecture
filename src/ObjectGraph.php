<?php declare(strict_types=1);


namespace Cspray\ArchDemo;

use Auryn\Injector;
use Cspray\ArchDemo\Middleware\ControllerActionRequestHandler;
use Doctrine\ORM\EntityManagerInterface;
use FastRoute\RouteParser\Std as StdRouteParser;
use FastRoute\RouteCollector;
use FastRoute\DataGenerator\GroupCountBased as GcbGenerator;
use FastRoute\Dispatcher\GroupCountBased as GcbDispatcher;
use Doctrine\ORM\Tools\Setup as DoctrineSetup;
use Doctrine\ORM\EntityManager;
use League\Fractal;

class ObjectGraph
{

    private $envConfig;

    public function __construct(Config\Environment $environmentConfig)
    {
        $this->envConfig = $environmentConfig;
    }

    /**
     * @return Injector
     * @throws \Auryn\ConfigException
     */
    public function createContainer() : Injector
    {
        $injector = new Injector();
        $injector->share($injector);
        $injector->share($this->envConfig);
        $injector->share($this->envConfig->databaseConfig());
        $injector->share($this->envConfig->corsConfig());
        $this->routerGraph($injector);
        $this->doctrineGraph($injector);
        $this->fractalGraph($injector);
        $this->middlewareGraph($injector);
        return $injector;
    }

    private function routerGraph(Injector $injector)
    {
        $injector->share(RouteCollector::class);
        $injector->define(RouteCollector::class, [
            'routeParser' => StdRouteParser::class,
            'dataGenerator' => GcbGenerator::class
        ]);
        $injector->share(Router\Router::class);
        $injector->define(Router\FastRoute\Router::class, [
            'collector' => RouteCollector::class,
            ':dispatcherCb' => function (array $data) use ($injector) {
                return $injector->make(GcbDispatcher::class, [':data' => $data]);
            }
        ]);
        $injector->alias(Router\Router::class, Router\FastRoute\Router::class);
    }

    private function doctrineGraph(Injector $injector)
    {
        $dbConfig = $this->envConfig->databaseConfig();
        $params = [
            'driver' => $dbConfig->driver(),
            'user' => $dbConfig->user(),
            'password' => $dbConfig->password(),
            'dbname' => $dbConfig->name(),
            'host' => $dbConfig->host()
        ];
        $configPath = [dirname(__DIR__) . '/config/doctrine'];
        $isDev = true;
        $config = DoctrineSetup::createXMLMetadataConfiguration($configPath, $isDev);
        $entityManager = EntityManager::create($params, $config);
        $injector->share($entityManager);
        $injector->alias(EntityManagerInterface::class, get_class($entityManager));
    }

    private function fractalGraph(Injector $injector)
    {
        $injector->share(Fractal\Manager::class);
        $injector->prepare(Fractal\Manager::class, function (Fractal\Manager $manager) {
            $manager->setSerializer(new Fractal\Serializer\DataArraySerializer());
        });
    }

    private function middlewareGraph(Injector $injector)
    {
        $injector->share(ControllerActionRequestHandler::class);
    }
}
