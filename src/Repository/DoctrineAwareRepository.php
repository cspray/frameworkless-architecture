<?php declare(strict_types=1);

namespace Cspray\ArchDemo\Repository;

use Cspray\ArchDemo\Entity\Entity;
use Cspray\ArchDemo\Exception\InvalidTypeException;
use Cspray\ArchDemo\Exception\NotFoundException;
use Doctrine\ORM\EntityManagerInterface;
use Ramsey\Uuid\UuidInterface;
use ArrayObject;
use Traversable;

abstract class DoctrineAwareRepository implements Repository {

    private $entityManager;
    private $repository;

    public function __construct(EntityManagerInterface $entityManager) {
        $this->entityManager = $entityManager;
        $this->repository = $entityManager->getRepository($this->getEntityClass());
    }

    public function findAll() : Traversable {
        return new ArrayObject($this->repository->findAll());
    }

    public function find(UuidInterface $uuid): Entity {
        $entity = $this->repository->find($uuid);
        if (!$entity) {
            throw new NotFoundException('Could not find a ' . $this->getEntityClass() . ' with ID ' . $uuid);
        }

        return $entity;
    }

    public function save(Entity $entity): bool {
        $entityClass = $this->getEntityClass();
        if (!$entity instanceof $entityClass) {
            throw new InvalidTypeException('Expected an entity with type ' . $entityClass . ' but got ' . get_class($entity));
        }

        $results = $entity->validate();
        if (!$results->isValid()) {
            return false;
        }

        $this->entityManager->persist($entity);
        $this->entityManager->flush();
        return true;
    }

    public function delete(UuidInterface $uuid): void {
        $dog = $this->repository->find($uuid);
        if ($dog) {
            $this->entityManager->remove($dog);
            $this->entityManager->flush();
        }
    }

    abstract protected function getEntityClass() : string;
}