<?php declare(strict_types=1);

use Cspray\ArchDemo\Controller\DogController;
use Cspray\ArchDemo\Router\FastRouteRouter;

return function(FastRouteRouter $router) {
    $router->get('/dogs', DogController::class . '#index');
    $router->post('/dogs', DogController::class . '#create');
};