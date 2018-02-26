<?php declare(strict_types=1);

namespace Cspray\ArchDemo\Test\Stub;

use Cspray\ArchDemo\Controller\NotFoundController;
use Cspray\ArchDemo\HttpStatusCodes;
use Cspray\ArchDemo\Router\ControllerAction;
use Cspray\ArchDemo\Router\ResolvedRoute;
use Cspray\ArchDemo\Router\Route;
use Cspray\ArchDemo\Router\Router;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\ServerRequest;

class RouterStub implements Router {

    private $routes = [];
    private $matchedRoute;

    public function __construct(ResolvedRoute $resolvedRoute = null) {
        $this->matchedRoute = $resolvedRoute ?? $this->defaultResolvedRoute();
    }

    private function defaultResolvedRoute() : ResolvedRoute {
        $req = new ServerRequest();
        return new ResolvedRoute($req, new ControllerAction(NotFoundController::class, 'index'), HttpStatusCodes::OK);
    }

    /**
     * The $regexPattern should match against the path of the ServerRequest's URI,
     *
     * @param string $httpMethod
     * @param string $regexPattern
     * @param ControllerAction $controllerAction
     * @return void
     */
    public function addRoute(string $httpMethod, string $regexPattern, ControllerAction $controllerAction) : void {
        $this->routes[] = new Route($httpMethod, $regexPattern, $controllerAction);
    }

    /**
     * Should always return a ResolvedRoute that includes the controller that
     * should be invoked
     *
     * @param ServerRequestInterface $request
     * @return ResolvedRoute
     */
    public function match(ServerRequestInterface $request): ResolvedRoute {
        return $this->matchedRoute;
    }

    /**
     * @return Route[]
     */
    public function getRoutes(): iterable {
        return $this->routes;
    }
}