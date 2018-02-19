<?php declare(strict_types=1);

namespace Cspray\ArchDemo\Model;

use Cspray\ArchDemo\Entity\Trainer;
use Cspray\ArchDemo\Exception\NotFoundException;
use Ramsey\Uuid\UuidInterface;
use Respect\Validation\Validator as v;
use Traversable;

class TrainerModel extends ApplicationModel {

    public function entityClass() : string {
        return Trainer::class;
    }

    protected function validationRules(): array {
        return [
            'name' => [
                'message' => 'Must contain only letters, spaces and be between 3 and 50 characters long',
                'rule' => v::stringType()->alpha(' ')->length(3, 50)
            ],
            'specialty' => [
                'message' => 'Must contain only letters, spaces and be between 3 and 50 characters long',
                'rule' => v::stringType()->alpha(' ')->length(3, 50)
            ],
        ];
    }

    /**
     * @return Trainer[]
     */
    public function all() : Traversable {
        return parent::all();
    }

    /**
     * @param string $uuid
     * @return Trainer
     * @throws NotFoundException
     */
    public function find(UuidInterface $uuid) : Trainer {
        return parent::find($uuid);
    }

    public function save(Trainer $trainer) : bool {
        return $this->doSave($trainer);
    }

    public function isValid(Trainer $trainer) : bool {
        return $this->doIsValidCheck($trainer);
    }

}