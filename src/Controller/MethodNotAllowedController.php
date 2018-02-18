<?php declare(strict_types=1);

namespace Cspray\ArchDemo\Controller;

use Cspray\ArchDemo\HttpStatusCodes;
use Psr\Http\Message\ResponseInterface;
use Zend\Diactoros\Response\JsonResponse;

class MethodNotAllowedController {

    public function index() : ResponseInterface {
        return new JsonResponse(['message' => 'Method Not Allowed'], HttpStatusCodes::METHOD_NOT_ALLOWED);
    }

}