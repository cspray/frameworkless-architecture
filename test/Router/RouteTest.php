<?php

/**
 *
 * @license See LICENSE in source root
 * @version 1.0
 * @since   1.0
 */

namespace Cspray\ArchDemo\Test\Router;

use Cspray\ArchDemo\Router\Route;
use Cspray\ArchDemo\Router\ControllerAction;
use PHPUnit\Framework\TestCase;

class RouteTest extends TestCase
{

    public function routeProvider()
    {
        $expected = "GET\t/handler-string\t\tcontroller#action";
        return [
            [new Route('GET', '/handler-string', new ControllerAction('controller', 'action')), $expected],

        ];
    }

    /**
     * @dataProvider routeProvider
     */
    public function testRouteToString($route, $expected)
    {
        $this->assertEquals($expected, (string) $route);
    }
}
