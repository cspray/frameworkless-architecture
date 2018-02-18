<?php declare(strict_types=1);

namespace Cspray\ArchDemo\Model;

use Cspray\ArchDemo\Entity\Entity;
use Cspray\ArchDemo\Exception\NotFoundException;
use Doctrine\ORM\EntityManagerInterface;
use Ramsey\Uuid\UuidInterface;
use Traversable;

abstract class ApplicationModel {

    protected $entityManager;
    protected $repository;
    private $validationRules;
    private $errors = [];

    public function __construct(EntityManagerInterface $entityManager) {
        $this->entityManager = $entityManager;
        $this->repository = $entityManager->getRepository($this->entityClass());
        $this->validationRules = $this->validationRules();
    }

    /**
     * @return Traversable
     */
    public function all() : Traversable {
        foreach ($this->repository->findAll() as $entity) {
            yield $entity;
        }
    }

    public function find(UuidInterface $uuid) {
        $entity = $this->repository->find($uuid);
        if (!$entity) {
            throw new NotFoundException('Could not find a ' . $this->entityClass() . ' with ID ' . $uuid->toString());
        }

        return $entity;
    }

    public function delete(UuidInterface $uuid) : void {
        $dog = $this->repository->find($uuid);
        if ($dog) {
            $this->entityManager->remove($dog);
            $this->entityManager->flush();
        }
    }

    public function errors() : array {
        return $this->errors ?? [];
    }

    protected function checkPassesRules($entity) {
        $errors = [];
        foreach ($this->validationRules as $attr => $ruleConfig) {
            $methodName = 'get' . ucfirst($attr);
            /** @var \Respect\Validation\Validator $rule */
            $rule = $ruleConfig['rule'];
            $value = $entity->$methodName();
            if (!$rule->validate($value)) {
                $errors[$attr] = $ruleConfig['message'];
            }
        }

        return $errors;
    }

    protected function doSave(Entity $entity) : bool {
        $isValid = $this->isValid($entity);
        if ($isValid) {
            $this->entityManager->persist($entity);
            $this->entityManager->flush();
        }

        return $isValid;
    }

    protected function doIsValidCheck(Entity $entity) : bool {
        $this->errors = $this->checkPassesRules($entity);
        return empty($this->errors);
    }

    abstract public function entityClass() : string;

    abstract protected function validationRules() : array;

}