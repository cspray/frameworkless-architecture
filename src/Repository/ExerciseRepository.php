<?php declare(strict_types=1);

namespace Cspray\ArchDemo\Repository;

use Cspray\ArchDemo\Entity\Exercise;

class ExerciseRepository extends DoctrineAwareRepository implements Repository {

    protected function getEntityClass(): string {
        return Exercise::class;
    }
}