<?php declare(strict_types=1);

namespace Cspray\ArchDemo\Controller;

use Cspray\ArchDemo\HttpStatusCodes;
use Psr\Http\Message\ResponseInterface;
use Zend\Diactoros\Response\JsonResponse;

class NotFoundController {

    public function index() : ResponseInterface {
        return new JsonResponse(['message' => 'Not Found'], HttpStatusCodes::NOT_FOUND);
    }

}