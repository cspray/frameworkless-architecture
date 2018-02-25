<?php declare(strict_types=1);

namespace Cspray\ArchDemo\Controller\Mixin;

use Cspray\ArchDemo\Repository\Repository;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Ramsey\Uuid\Uuid;
use Zend\Diactoros\Response\JsonResponse;

trait ShowAwareMixin {

    public function show(ServerRequestInterface $request) : ResponseInterface {
        $uuid = Uuid::fromString($request->getAttribute('id'));
        $entity = $this->getRepository()->find($uuid);
        return new JsonResponse($this->serialize($entity));
    }

    abstract protected function getRepository() : Repository;

    abstract protected function serialize($entity);

}