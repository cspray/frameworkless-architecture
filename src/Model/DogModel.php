<?php declare(strict_types=1);

namespace Cspray\ArchDemo\Model;

use Cspray\ArchDemo\Entity\Dog;
use Cspray\ArchDemo\Exception\NotFoundException;
use Ramsey\Uuid\UuidInterface;
use Respect\Validation\Validator as v;
use Traversable;

class DogModel extends ApplicationModel {

    public function entityClass(): string {
        return Dog::class;
    }

    protected function validationRules(): array {
        return [
            'name' => [
                'message' => 'Must contain only letters, spaces and be between 3 and 50 characters long',
                'rule' => v::stringType()->alpha(' ')->length(3, 50)
            ],
            'breed' => [
                'message' => 'Must contain only letters, spaces and be between 3 and 50 characters long',
                'rule' => v::stringType()->alpha(' ')->length(3, 50)
            ],
            'age' => [
                'message' => 'Must be a positive number greater than 0 and less than or equal to 50',
                'rule' => v::intType()->between(1, 49)
            ]
        ];
    }

    /**
     * @return Dog[]
     */
    public function all() : Traversable {
        return parent::all();
    }

    /**
     * @param string $uuid
     * @return Dog
     * @throws NotFoundException
     */
    public function find(UuidInterface $uuid) : Dog {
        return parent::find($uuid);
    }

    public function save(Dog $dog) : bool {
        return $this->doSave($dog);
    }

    public function isValid(Dog $dog) : bool {
        return $this->doIsValidCheck($dog);
    }

}