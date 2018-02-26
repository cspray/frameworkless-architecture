<?php declare(strict_types=1);

require_once __DIR__ . '/vendor/autoload.php';

use Cspray\ArchDemo\Middleware\ControllerActionRequestHandler;
use Cspray\ArchDemo\HttpStatusCodes;
use Equip\Dispatch\MiddlewareCollection;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\JsonResponse;
use Zend\Diactoros\Response\SapiEmitter;
use Zend\Diactoros\ServerRequestFactory;
use function Cspray\ArchDemo\bootstrap;

try {
    $injector = bootstrap($_ENV['APP_ENV'] ?? 'development');

    $routesFunction = require(__DIR__ . '/config/routes.php');

    $injector->execute($routesFunction);

    $middlewaresFunction = require(__DIR__ . '/config/middlewares.php');
    $middlewares = new MiddlewareCollection();

    $injector->execute($middlewaresFunction, [':middlewares' => $middlewares]);

    $defaultHandler = function(ServerRequestInterface $request) use($injector) {
        return $injector->make(ControllerActionRequestHandler::class)->handle($request);
    };

    $request = ServerRequestFactory::fromGlobals();
    $response = $middlewares->dispatch($request, $defaultHandler);
} catch (Throwable $error) {
    $response = new JsonResponse(['message' => 'Internal Server Error'], HttpStatusCodes::INTERNAL_SERVER_ERROR);
} finally {
    (new SapiEmitter())->emit($response);
}
