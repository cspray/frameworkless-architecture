<?php declare(strict_types=1);

namespace Cspray\ArchDemo\Repository;

use Cspray\ArchDemo\Entity\Trainer;

class TrainerRepository extends Doctrine\BaseRepository implements Repository {

    protected function getEntityClass(): string {
        return Trainer::class;
    }

}