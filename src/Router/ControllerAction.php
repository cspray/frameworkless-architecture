<?php declare(strict_types=1);

namespace Cspray\ArchDemo\Router;

class ControllerAction
{

    private $controller;
    private $action;

    public function __construct(string $controllerClassName, string $controllerMethodName)
    {
        $this->controller = $controllerClassName;
        $this->action = $controllerMethodName;
    }

    public function getController() : string
    {
        return $this->controller;
    }

    public function getAction() : string
    {
        return $this->action;
    }

    public function __toString()
    {
        return $this->controller . "#" . $this->action;
    }
}
