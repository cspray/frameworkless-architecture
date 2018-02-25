<?php declare(strict_types=1);

namespace Cspray\ArchDemo\Controller\Mixin;

use Cspray\ArchDemo\Entity\Entity;
use Cspray\ArchDemo\HttpStatusCodes;
use Cspray\ArchDemo\Repository\Repository;
use Cspray\ArchDemo\Validation\Results as ValidationResults;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\JsonResponse;

trait CreateAwareMixin {

    public function create(ServerRequestInterface $request) : ResponseInterface {
        $entity = $this->createNewEntity($request);
        $validationResults = $entity->validate();
        if (!$validationResults->isValid()) {
            return $this->responseForValidationError($validationResults);
        }

        $this->getRepository()->save($entity);
        return new JsonResponse($this->serialize($entity), HttpStatusCodes::CREATED);
    }

    abstract protected function createNewEntity(ServerRequestInterface $request) : Entity;

    abstract protected function getRepository() : Repository;

    abstract protected function responseForValidationError(ValidationResults $results) : ResponseInterface;

    abstract protected function serialize($entity);

}