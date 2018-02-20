<?php declare(strict_types=1);

use Cspray\ArchDemo\Controller\DogController;
use Cspray\ArchDemo\Router\FastRouteRouter;

return function(FastRouteRouter $router) {
    $router->get('/dogs', DogController::class . '#index');
    $router->get('/dogs/{id}', DogController::class . '#show');
    $router->post('/dogs', DogController::class . '#create');
};