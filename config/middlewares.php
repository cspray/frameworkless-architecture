<?php declare(strict_types=1);

/**
 * Add your PSR-15 compliant middleware into the MiddlewareCollection; default Middleware provided by the
 * framework will be added regardless of anything provided here.
 */

use Auryn\Injector;
use Cspray\ArchDemo\Middleware\CorsMiddleware;
use Cspray\ArchDemo\Middleware\ParsedRequestBodyMiddleware;
use Equip\Dispatch\MiddlewareCollection;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Middlewares\AccessLog;

return function(MiddlewareCollection $middlewares, Injector $injector) {
    /* !! DO NOT CHANGE THIS OR YOUR APPLICATION MAY NOT WORK PROPERLY !! */
    $corsMiddleware = $injector->make(CorsMiddleware::class);
    $parsedRequestBodyMiddleware = $injector->make(ParsedRequestBodyMiddleware::class);

    $accessLogger = (new Logger('access'))->pushHandler(new StreamHandler(fopen('php://stdout', 'r+')));
    $accessLogMiddleware = new AccessLog($accessLogger);

    $whoops = new \Whoops\Run();
    $whoops->pushHandler(new \Whoops\Handler\JsonResponseHandler());
    $whoopsMiddleware = new \Middlewares\Whoops($whoops);

    $middlewares->append($accessLogMiddleware);
    $middlewares->append($whoopsMiddleware);
    $middlewares->append($corsMiddleware);
    $middlewares->append($parsedRequestBodyMiddleware);
    /* !! DO NOT CHANGE THIS OR YOUR APPLICATION MAY NOT WORK PROPERLY !! */

    /* GOOD TO GO! ADD YOUR OWN MIDDLEWARE AFTER THIS */
};