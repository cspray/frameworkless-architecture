<?php declare(strict_types=1);

/**
 * Add your PSR-15 compliant middleware into the MiddlewareCollection; default Middleware provided by the
 * framework will be added regardless of anything provided here.
 */

use Auryn\Injector;
use Cspray\ArchDemo\Middleware\CorsMiddleware;
use Equip\Dispatch\MiddlewareCollection;


return function(MiddlewareCollection $middlewares, Injector $injector) {
    $corsMiddleware = $injector->make(CorsMiddleware::class);

    $middlewares->append($corsMiddleware);

};