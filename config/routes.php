<?php declare(strict_types=1);

use Cspray\ArchDemo\Controller\DogController;
use Cspray\ArchDemo\Router\FriendlyRouter;
use Cspray\ArchDemo\Router\Router;

return function(Router $router) {
    $router = new FriendlyRouter($router);
    $router->resource('dogs', DogController::class);
};