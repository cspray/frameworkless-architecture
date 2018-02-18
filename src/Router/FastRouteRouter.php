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

namespace Cspray\ArchDemo\Router;

use Cspray\ArchDemo\{
    Controller\MethodNotAllowedController as MethodNotAllowedController,
    Controller\NotFoundController as NotFoundController,
    HttpStatusCodes,
    Exception\InvalidArgumentException,
    Exception\InvalidTypeException
};
use FastRoute\{Dispatcher, RouteCollector};
use Psr\Http\Message\{
    ServerRequestInterface
};

class FastRouteRouter implements Router {

    private $dispatcherCb;
    private $collector;
    private $routes = [];
    private $notFoundControllerAction;
    private $methodNotFoundControllerAction;
    private $mountedPrefix = [];

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
    public function __construct(RouteCollector $collector, callable $dispatcherCb) {
        $this->collector = $collector;
        $this->dispatcherCb = $dispatcherCb;
    }

    private function normalizeControllerActionString(string $prettyString) : ControllerAction {
        if (substr_count($prettyString, '#') === 0) {
            throw new InvalidArgumentException("An invalid route controller#action was passed, got " . $prettyString);
        }
        list($controller, $action) = explode('#', $prettyString);
        return new ControllerAction($controller, $action);
    }
    /**
     * @param string $pattern
     * @param mixed $handler
     * @return $this
     */
    public function get(string $pattern, $handler) : self {
        $controllerAction = $this->normalizeControllerActionString($handler);
        return $this->addRoute('GET', $pattern, $controllerAction);
    }

    /**
     * @param string $pattern
     * @param mixed $handler
     * @return $this
     */
    public function post(string $pattern, $handler) : self {
        $controllerAction = $this->normalizeControllerActionString($handler);
        return $this->addRoute('POST', $pattern, $controllerAction);
    }

    /**
     * @param string $pattern
     * @param mixed $handler
     * @return $this
     */
    public function put(string $pattern, $handler) : self {
        $controllerAction = $this->normalizeControllerActionString($handler);
        return $this->addRoute('PUT', $pattern, $controllerAction);
    }

    /**
     * @param string $pattern
     * @param mixed $handler
     * @return $this
     */
    public function delete(string $pattern, $handler) : self {
        $controllerAction = $this->normalizeControllerActionString($handler);
        return $this->addRoute('DELETE', $pattern, $controllerAction);
    }

    /**
     * Allows you to easily prefix routes to composer complex URL patterns without
     * constantly retyping pattern matches.
     *
     * @param string $prefix
     * @param callable $cb
     * @return $this
     */
    public function mount(string $prefix, callable $cb) : self {
        $this->mountedPrefix[] = $prefix;
        $cb($this);
        $this->mountedPrefix = [];
        return $this;
    }

    /**
     * @return string
     */
    public function root() : string {
        return $this->isMounted() ? '' : '/';
    }

    /**
     * @return bool
     */
    public function isMounted() : bool {
        return !empty($this->mountedPrefix);
    }

    /**
     * @param $method
     * @param $pattern
     * @param $handler
     * @return $this
     */
    public function addRoute(string $method, string $pattern, ControllerAction $controllerAction) : self {
        if ($this->isMounted()) {
            $pattern = implode('', $this->mountedPrefix) . $pattern;
        }
        $this->routes[] = new Route($pattern, $method, $controllerAction);
        $this->collector->addRoute($method, $pattern, $controllerAction);
        return $this;
    }

    /**
     * @param ServerRequestInterface $request
     * @return ResolvedRoute
     * @throws InvalidArgumentException
     */
    public function match(ServerRequestInterface $request) : ResolvedRoute {
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
    private function guardNotOkMatch(ServerRequestInterface $request, int $status, array $route) {
        if (empty($route) || $status === Dispatcher::NOT_FOUND) {
            return new ResolvedRoute($request, $this->getNotFoundControllerAction(), HttpStatusCodes::NOT_FOUND);
        }

        if ($status === Dispatcher::METHOD_NOT_ALLOWED) {
            return new ResolvedRoute($request, $this->getMethodNotAllowedControllerAction(), HttpStatusCodes::METHOD_NOT_ALLOWED, $route[0]);
        }

        return null;
    }

    /**
     * @return Dispatcher
     * @throws InvalidTypeException
     */
    private function getDispatcher() : Dispatcher {
        $cb = $this->dispatcherCb;
        $dispatcher = $cb($this->collector->getData());
        if (!$dispatcher instanceof Dispatcher) {
            $msg = 'A FastRoute\\Dispatcher must be returned from dispatcher callback injected in constructor';
            throw new InvalidTypeException($msg);
        }

        return $dispatcher;
    }

    public function getRoutes() : array {
        return $this->routes;
    }

    /**
     * Returns the controller/action that will be invoked when the URL requested could not be found.
     *
     * @return string
     */
    public function getNotFoundControllerAction() : ControllerAction {
        if (!$this->notFoundControllerAction) {
            $this->setNotFoundControllerAction(new ControllerAction(NotFoundController::class, 'index'));
        }

        return $this->notFoundControllerAction;
    }

    /**
     * This function GUARANTEES that a callable will always be returned.
     *
     * @return callable
     */
    public function getMethodNotAllowedControllerAction() : ControllerAction {
        if (!$this->methodNotFoundControllerAction) {
            $this->setMethodNotAllowedControllerAction(new ControllerAction(MethodNotAllowedController::class, 'index'));
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
    public function setNotFoundControllerAction(ControllerAction $controllerAction) : self {
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
    public function setMethodNotAllowedControllerAction(ControllerAction $controllerAction) : self {
        $this->methodNotFoundControllerAction = $controllerAction;
        return $this;
    }

}
