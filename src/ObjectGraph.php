<?php declare(strict_types=1);


namespace Cspray\ArchDemo;

use Auryn\Injector;
use Cspray\ArchDemo\Middleware\ControllerActionRequestHandler;
use Cspray\ArchDemo\Model\DogModel;
use Doctrine\ORM\EntityManagerInterface;
use FastRoute\RouteParser\Std as StdRouteParser;
use FastRoute\RouteCollector;
use FastRoute\DataGenerator\GroupCountBased as GcbGenerator;
use FastRoute\Dispatcher\GroupCountBased as GcbDispatcher;
use Doctrine\ORM\Tools\Setup as DoctrineSetup;
use Doctrine\ORM\EntityManager;
use League\Fractal;

class ObjectGraph {

    private $envConfig;

    public function __construct(Config\Environment $environmentConfig) {
        $this->envConfig = $environmentConfig;
    }

    /**
     * @return Injector
     * @throws \Auryn\ConfigException
     */
    public function createContainer() : Injector {
        $injector = new Injector();
        $injector->share($injector);
        $this->routerGraph($injector);
        $this->doctrineGraph($injector);
        $this->fractalGraph($injector);
        $this->middlewareGraph($injector);
        return $injector;
    }

    private function routerGraph(Injector $injector) {
        $injector->share(RouteCollector::class);
        $injector->define(RouteCollector::class, [
            'routeParser' => StdRouteParser::class,
            'dataGenerator' => GcbGenerator::class
        ]);
        $injector->share(Router\FastRouteRouter::class);
        $injector->define(Router\FastRouteRouter::class, [
            'collector' => RouteCollector::class,
            ':dispatcherCb' => function(array $data) use($injector) {
                return $injector->make(GcbDispatcher::class, [':data' => $data]);
            }
        ]);
        $injector->alias(Router\Router::class, Router\FastRouteRouter::class);
    }

    private function doctrineGraph(Injector $injector) {
        $params = [
            'driver' => $this->envConfig->databaseDriver(),
            'user' => $this->envConfig->databaseUser(),
            'password' => $this->envConfig->databasePassword(),
            'dbname' => $this->envConfig->databaseName(),
            'host' => $this->envConfig->databaseHost()
        ];
        $configPath = [dirname(__DIR__) . '/config/doctrine'];
        $isDev = true;
        $config = DoctrineSetup::createXMLMetadataConfiguration($configPath, $isDev);
        $entityManager = EntityManager::create($params, $config);
        $injector->share($entityManager);
        $injector->alias(EntityManagerInterface::class, get_class($entityManager));
    }

    private function fractalGraph(Injector $injector) {
        $injector->share(Fractal\Manager::class);
        $injector->prepare(Fractal\Manager::class, function(Fractal\Manager $manager) {
            $manager->setSerializer(new Fractal\Serializer\DataArraySerializer());
        });
    }

    private function middlewareGraph(Injector $injector) {
        $injector->share(ControllerActionRequestHandler::class);
    }

}