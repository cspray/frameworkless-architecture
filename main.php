<?php

require_once __DIR__ . '/vendor/autoload.php';

$injector = \Cspray\ArchDemo\bootstrap($_ENV['APP_ENV'] ?? 'development');

$routesFunction = require(__DIR__ . '/config/routes.php');

$injector->execute($routesFunction);

$middlewaresFunction = require(__DIR__ . '/config/middlewares.php');
$middlewares = new \Equip\Dispatch\MiddlewareCollection();

$middlewares->append(new \Cspray\ArchDemo\Middleware\ParsedRequestBodyMiddleware());

$injector->execute($middlewaresFunction, [':middlewares' => $middlewares]);

$defaultHandler = function(\Psr\Http\Message\ServerRequestInterface $request) use($injector) {
    return $injector->make(\Cspray\ArchDemo\Middleware\ControllerActionRequestHandler::class)->handle($request);
};

$request = \Zend\Diactoros\ServerRequestFactory::fromGlobals();
$response = $middlewares->dispatch($request, $defaultHandler);

$sapiEmitter = new \Zend\Diactoros\Response\SapiEmitter();

$sapiEmitter->emit($response);