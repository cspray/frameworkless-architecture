<?php declare(strict_types=1);

namespace Cspray\ArchDemo\Controller\Mixin;

use Cspray\ArchDemo\Repository\Repository;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Ramsey\Uuid\Uuid;
use Zend\Diactoros\Response\EmptyResponse;

trait DeleteAwareMixin {

    public function delete(ServerRequestInterface $request) : ResponseInterface {
        $id = Uuid::fromString($request->getAttribute('id'));
        $this->getRepository()->delete($id);
        return new EmptyResponse();
    }

    abstract protected function getRepository() : Repository;
}