<?php

declare(strict_types=1);

/**
 * An object that represents what HTTP Request data should be mapped to which handler.
 *
 * @license See LICENSE in source root
 */

namespace Cspray\ArchDemo\Router;


class Route {

    private $pattern;
    private $method;
    private $controllerAction;

    /**
     * @param string $pattern
     * @param string $method
     * @param ControllerAction $controllerAction
     */
    public function __construct(
        string $pattern,
        string $method,
        ControllerAction $controllerAction
    ) {
        $this->pattern = $pattern;
        $this->method = $method;
        $this->controllerAction = $controllerAction;
    }

    /**
     * @return string
     */
    public function getPattern() : string {
        return $this->pattern;
    }

    /**
     * @return string
     */
    public function getMethod() : string {
        return $this->method;
    }

    /**
     * @return mixed
     */
    public function getControllerAction() : ControllerAction {
        return $this->controllerAction;
    }

    /**
     * @return string
     */
    public function __toString() : string {
        $format = "%s\t%s\t\t%s";
        $handler = $this->getNormalizedHandler($this->controllerAction);
        return sprintf($format, $this->method, $this->pattern, $handler);
    }

    private function getNormalizedHandler(ControllerAction $controllerAction) : string {
        return $controllerAction->getController() . '#' . $controllerAction->getAction();
    }

}
