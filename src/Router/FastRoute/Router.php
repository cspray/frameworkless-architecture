<?php

declare(strict_types=1);

/**
 * A router that is a wrapper around the FastRoute library that adheres to
 * Router interface.
 *
 * @license See LICENSE in source root
 *
 * @see https://github.com/nikic/FastRoute
 */

namespace Cspray\ArchDemo\Router\FastRoute;

use Cspray\ArchDemo\Router\ControllerAction;
use Cspray\ArchDemo\Router\ResolvedRoute;
use Cspray\ArchDemo\Router\Route;
use Cspray\ArchDemo\Router\Router as ArchDemoRouter;
use Cspray\ArchDemo\Controller\MethodNotAllowedController;
use Cspray\ArchDemo\Controller\NotFoundController;
use Cspray\ArchDemo\HttpStatusCodes;
use Cspray\ArchDemo\Exception\InvalidArgumentException;
use Cspray\ArchDemo\Exception\InvalidTypeException;
use FastRoute\Dispatcher;
use FastRoute\RouteCollector;
use Psr\Http\Message\ServerRequestInterface;

class Router implements ArchDemoRouter
{

    private $dispatcherCb;
    private $collector;
    private $routes = [];
    private $notFoundControllerAction;
    private $methodNotFoundControllerAction;

    /**
     * Pass a HandlerResolver, a FastRoute\RouteCollector and a callback that
     * returns a FastRoute\Dispatcher.
     *
     * We ask for a callback instead of the object itself to work around needing
     * the list of routes at FastRoute dispatcher instantiation. The $dispatcherCb is
     * invoked when Router::match is called and it should expect an array of data
     * in the same format as $collector->getData().
     *
     * @param RouteCollector $collector
     * @param callable $dispatcherCb
     */
    public function __construct(RouteCollector $collector, callable $dispatcherCb)
    {
        $this->collector = $collector;
        $this->dispatcherCb = $dispatcherCb;
    }

    /**
     * @param string $method
     * @param string $pattern
     * @param ControllerAction $controllerAction
     * @return void
     */
    public function addRoute(string $method, string $pattern, ControllerAction $controllerAction) : void
    {
        $this->routes[] = new Route($method, $pattern, $controllerAction);
        $this->collector->addRoute($method, $pattern, $controllerAction);
    }

    /**
     * @param ServerRequestInterface $request
     * @return ResolvedRoute
     * @throws InvalidArgumentException
     */
    public function match(ServerRequestInterface $request) : ResolvedRoute
    {
        $uri = $request->getUri();
        $path = empty($uri->getPath()) ? '/' : $uri->getPath();
        $route = $this->getDispatcher()->dispatch($request->getMethod(), $path);
        $status = array_shift($route);

        if ($notOkResolved = $this->guardNotOkMatch($request, $status, $route)) {
            return $notOkResolved;
        }

        list($handler, $params) = $route;

        $request = $request->withAttribute('_arch_demo', ['handler' => (string) $handler]);
        foreach ($params as $k => $v) {
            $request = $request->withAttribute($k, rawurldecode($v));
        }

        return new ResolvedRoute($request, $handler, HttpStatusCodes::OK);
    }

    /**
     * @param ServerRequestInterface $request
     * @param integer $status
     * @param array $route
     * @return ResolvedRoute|null
     */
    private function guardNotOkMatch(ServerRequestInterface $request, int $status, array $route)
    {
        if (empty($route) || $status === Dispatcher::NOT_FOUND) {
            return new ResolvedRoute($request, $this->getNotFoundControllerAction(), HttpStatusCodes::NOT_FOUND);
        }

        if ($status === Dispatcher::METHOD_NOT_ALLOWED) {
            return new ResolvedRoute(
                $request,
                $this->getMethodNotAllowedControllerAction(),
                HttpStatusCodes::METHOD_NOT_ALLOWED,
                $route[0]
            );
        }

        return null;
    }

    /**
     * @return Dispatcher
     * @throws InvalidTypeException
     */
    private function getDispatcher() : Dispatcher
    {
        $cb = $this->dispatcherCb;
        $dispatcher = $cb($this->collector->getData());
        if (!$dispatcher instanceof Dispatcher) {
            $msg = 'A FastRoute\\Dispatcher must be returned from dispatcher callback injected in constructor';
            throw new InvalidTypeException($msg);
        }

        return $dispatcher;
    }

    public function getRoutes() : array
    {
        return $this->routes;
    }

    /**
     * Returns the controller/action that will be invoked when the URL requested could not be found.
     *
     * @return ControllerAction
     */
    public function getNotFoundControllerAction() : ControllerAction
    {
        if (!$this->notFoundControllerAction) {
            $this->setNotFoundControllerAction(new ControllerAction(NotFoundController::class, 'index'));
        }

        return $this->notFoundControllerAction;
    }

    /**
     * @return ControllerAction
     */
    public function getMethodNotAllowedControllerAction() : ControllerAction
    {
        if (!$this->methodNotFoundControllerAction) {
            $controllerAction = new ControllerAction(MethodNotAllowedController::class, 'index');
            $this->setMethodNotAllowedControllerAction($controllerAction);
        }
        return $this->methodNotFoundControllerAction;
    }

    /**
     * Set the $controller that will be passed to the resolved route when a
     * handler could not be found for a given request.
     *
     * @param callable $controller
     * @return $this
     */
    public function setNotFoundControllerAction(ControllerAction $controllerAction) : self
    {
        $this->notFoundControllerAction = $controllerAction;
        return $this;
    }

    /**
     * Set the controller that will be passed to the resolved route when a handler
     * is found for a given request but the HTTP method is not allowed.
     *
     * @param callable $controller
     * @return $this
     */
    public function setMethodNotAllowedControllerAction(ControllerAction $controllerAction) : self
    {
        $this->methodNotFoundControllerAction = $controllerAction;
        return $this;
    }
}
