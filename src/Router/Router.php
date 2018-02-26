<?php

declare(strict_types=1);

/**
 * Interface to determine the ControllerAction and dynamic URL parameters for a PSR-7 ServerRequest.
 *
 * @license See LICENSE in source root
 */

namespace Cspray\ArchDemo\Router;

use Psr\Http\Message\ServerRequestInterface;

interface Router {

    /**
     * The $regexPattern should match against the path of the ServerRequest's URI,
     *
     * @param string $httpMethod
     * @param string $regexPattern
     * @param ControllerAction $controllerAction
     * @return void
     */
    public function addRoute(string $httpMethod, string $regexPattern, ControllerAction $controllerAction) : void;

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
    public function getRoutes() : iterable;

}
