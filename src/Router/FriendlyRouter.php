<?php declare(strict_types=1);

namespace Cspray\ArchDemo\Router;

use Cspray\ArchDemo\Exception\InvalidArgumentException;
use Psr\Http\Message\ServerRequestInterface;

class FriendlyRouter implements Router {

    private $mountedPrefix = [];
    private $router;

    public function __construct(Router $router) {
        $this->router = $router;
    }

    private function normalizeControllerActionString(string $prettyString) : ControllerAction {
        if (substr_count($prettyString, '#') === 0) {
            throw new InvalidArgumentException("An invalid route controller#action was passed, got " . $prettyString);
        }
        list($controller, $action) = explode('#', $prettyString);
        return new ControllerAction($controller, $action);
    }

    public function resource(string $resourceName, string $controllerName) {
        $this->mount('/' . $resourceName, function(FriendlyRouter $router) use($controllerName) {
            $router->get($router->root(), $controllerName . '#index')
                   ->get('/{id}', $controllerName . '#show')
                   ->post($router->root(), $controllerName . '#create')
                   ->put('/{id}', $controllerName . '#update')
                   ->delete('/{id}', $controllerName . '#delete');
        });
    }

    /**
     * @param string $pattern
     * @param mixed $handler
     * @return $this
     */
    public function get(string $pattern, $handler) : self {
        $controllerAction = $this->normalizeControllerActionString($handler);
        $this->addRoute('GET', $pattern, $controllerAction);
        return $this;
    }

    /**
     * @param string $pattern
     * @param mixed $handler
     * @return $this
     */
    public function post(string $pattern, $handler) : self {
        $controllerAction = $this->normalizeControllerActionString($handler);
        $this->addRoute('POST', $pattern, $controllerAction);
        return $this;
    }

    /**
     * @param string $pattern
     * @param mixed $handler
     * @return $this
     */
    public function put(string $pattern, $handler) : self {
        $controllerAction = $this->normalizeControllerActionString($handler);
        $this->addRoute('PUT', $pattern, $controllerAction);
        return $this;
    }

    /**
     * @param string $pattern
     * @param mixed $handler
     * @return $this
     */
    public function delete(string $pattern, $handler) : self {
        $controllerAction = $this->normalizeControllerActionString($handler);
        $this->addRoute('DELETE', $pattern, $controllerAction);
        return $this;
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
     * The $regexPattern should match against the path of the ServerRequest's URI,
     *
     * @param string $httpMethod
     * @param string $regexPattern
     * @param ControllerAction $controllerAction
     * @return void
     */
    public function addRoute(string $httpMethod, string $regexPattern, ControllerAction $controllerAction) : void {
        if ($this->isMounted()) {
            $regexPattern = implode('', $this->mountedPrefix) . $regexPattern;
        }
        $this->router->addRoute($httpMethod, $regexPattern, $controllerAction);
    }

    /**
     * Should always return a ResolvedRoute that includes the controller that
     * should be invoked
     *
     * @param ServerRequestInterface $request
     * @return ResolvedRoute
     */
    public function match(ServerRequestInterface $request): ResolvedRoute {
        return $this->router->match($request);
    }

    /**
     * @return Route[]
     */
    public function getRoutes(): iterable {
        return $this->router->getRoutes();
    }
}