<?php declare(strict_types=1);

namespace Cspray\ArchDemo\Controller\Mixin;

use Cspray\ArchDemo\Repository\Repository;
use Psr\Http\Message\ResponseInterface;
use Zend\Diactoros\Response\JsonResponse;

trait IndexAwareMixin
{

    public function index() : ResponseInterface
    {
        $entities = $this->getRepository()->findAll();
        return new JsonResponse($this->serialize($entities));
    }

    abstract protected function getRepository() : Repository;

    abstract protected function serialize($entities);
}
