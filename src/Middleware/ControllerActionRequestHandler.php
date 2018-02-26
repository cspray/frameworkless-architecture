<?php declare(strict_types=1);


namespace Cspray\ArchDemo\Middleware;

use Auryn\Injector;
use Cspray\ArchDemo\Router\Router;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class ControllerActionRequestHandler implements RequestHandlerInterface
{

    private $router;
    private $injector;

    public function __construct(Router $router, Injector $injector)
    {
        $this->router = $router;
        $this->injector = $injector;
    }

    /**
     * Handle the request and return a response.
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $resolvedRoute = $this->router->match($request);
        $controllerAction = $resolvedRoute->getControllerAction();
        $controller = $this->injector->make($controllerAction->getController());
        $action = $controllerAction->getAction();

        return $controller->$action($resolvedRoute->getRequest());
    }
}
