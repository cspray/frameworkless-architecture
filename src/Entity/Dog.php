<?php declare(strict_types=1);

namespace Cspray\ArchDemo\Entity;

use Ramsey\Uuid\Uuid;

class Dog implements Entity {

    private $id;
    private $name;
    private $breed;
    private $age = 0;

    public function __construct() {
        $this->id = Uuid::uuid4();
    }

    public function getId() {
        return $this->id;
    }

    public function getName() {
        return $this->name;
    }

    public function getBreed() {
        return $this->breed;
    }

    public function getAge() {
        return $this->age;
    }

    public function withName(string $name) : Dog {
        $newDog = clone $this;
        $newDog->name = $name;
        return $newDog;
    }

    public function withBreed(string $breed) : Dog {
        $newDog = clone $this;
        $newDog->breed = $breed;
        return $newDog;
    }

    public function withIncrementedAge(int $numberOfYears) : Dog {
        $newDog = clone $this;
        $newDog->age = $newDog->age + $numberOfYears;
        return $newDog;
    }
}