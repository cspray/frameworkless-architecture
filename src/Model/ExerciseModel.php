<?php declare(strict_types=1);


namespace Cspray\ArchDemo\Model;

use Cspray\ArchDemo\Entity\Exercise;
use Ramsey\Uuid\UuidInterface;
use Respect\Validation\Validator as v;
use Traversable;

class ExerciseModel extends ApplicationModel {

    public function entityClass(): string {
        return Exercise::class;
    }

    protected function validationRules(): array {
        return [
            'name' => [
                'message' => 'Must contain only letters, spaces and be between 3 and 50 characters long',
                'rule' => v::stringType()->alpha(' ')->length(3, 50)
            ],
            'description' => [
                'message' => 'Must be greater than 3 and fewer than 1,000 characters long',
                'rule' => v::stringType()->alpha(' ')->length(3, 999)
            ]

        ];
    }

    /**
     * @return Exercise[]
     */
    public function all() : Traversable {
        return parent::all();
    }

    public function find(UuidInterface $uuid) : Exercise {
        return parent::find($uuid);
    }

    public function save(Exercise $exercise) : bool {
        return $this->doSave($exercise);
    }

    public function isValid(Exercise $exercise) : bool {
        return $this->doIsValidCheck($exercise);
    }
}