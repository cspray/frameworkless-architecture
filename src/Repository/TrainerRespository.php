<?php declare(strict_types=1);

namespace Cspray\ArchDemo\Repository;

use Cspray\ArchDemo\Entity\Trainer;

class TrainerRespository extends DoctrineAwareRepository implements Repository {

    protected function getEntityClass(): string {
        return Trainer::class;
    }

}