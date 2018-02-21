<?php declare(strict_types=1);

namespace Cspray\ArchDemo\Repository;

use Cspray\ArchDemo\Entity\Dog;

class DogRepository extends DoctrineAwareRepository implements Repository {

    protected function getEntityClass(): string {
        return Dog::class;
    }

}