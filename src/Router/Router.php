<?php

declare(strict_types=1);

/**
 * Interface to determine the controller to invoke for a given Request.
 *
 * @license See LICENSE in source root
 */

namespace Cspray\ArchDemo\Router;

use Cspray\Labrador\Http\Middleware\Middleware;
use Psr\Http\Message\ServerRequestInterface;

/**
 * The $handler set in methods can be an arbitrary value; the value that you set
 * should be parseable by the HandlerResolver you use when wiring up Labrador.
 */
interface Router {

    /**
     * @param string $httpMethod
     * @param string $regexPattern
     * @param ControllerAction $controllerAction
     * @return $this
     */
    public function addRoute(string $httpMethod, string $regexPattern, ControllerAction $controllerAction);

    /**
     * Should always return a ResolvedRoute that includes the controller that
     * should be invoked
     *
     * @param ServerRequestInterface $request
     * @return ResolvedRoute
     */
    public function match(ServerRequestInterface $request) : ResolvedRoute;

    /**
     * @return Route[]
     */
    public function getRoutes() : array;

}
