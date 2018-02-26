<?php declare(strict_types=1);

namespace Cspray\ArchDemo\Repository;

use Cspray\ArchDemo\Entity\Entity;
use Ramsey\Uuid\UuidInterface;
use Traversable;

interface Repository
{

    public function find(UuidInterface $uuid) : Entity;

    public function findAll() : Traversable;

    public function save(Entity $entity) : bool;

    public function delete(UuidInterface $uuid) : void;
}
