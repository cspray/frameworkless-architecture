<?php declare(strict_types=1);

namespace Cspray\ArchDemo\Controller\Mixin;

use Cspray\ArchDemo\Entity\Entity;
use Cspray\ArchDemo\Repository\Repository;
use Cspray\ArchDemo\Validation\Results as ValidationResults;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Ramsey\Uuid\Uuid;
use Zend\Diactoros\Response\JsonResponse;

trait UpdateAwareMixin {

    public function update(ServerRequestInterface $request) : ResponseInterface {
        $id = Uuid::fromString($request->getAttribute('id'));
        $entity = $this->getRepository()->find($id);
        $entity = $this->updateExistingEntity($entity, $request);
        $validationResults = $entity->validate();
        if (!$validationResults->isValid()) {
            return $this->responseForValidationError($validationResults);
        }

        $this->getRepository()->save($entity);
        return new JsonResponse($this->serialize($entity));
    }

    abstract protected function updateExistingEntity(Entity $entity, ServerRequestInterface $request) : Entity;

    abstract protected function getRepository() : Repository;

    abstract protected function responseForValidationError(ValidationResults $results) : ResponseInterface;

    abstract protected function serialize($entity);

}