<?php declare(strict_types=1);

namespace Cspray\ArchDemo\Entity;

use Ramsey\Uuid\Uuid;

class Exercise implements Entity {

    private $id;
    private $name;
    private $description;

    public function __construct() {
        $this->id = Uuid::uuid4();
    }

    public function getId() {
        return $this->id;
    }

    public function getName() {
        return $this->name;
    }

    public function getDescription() {
        return $this->description;
    }

    public function withName(string $name) : Exercise {
        $newExercise = clone $this;
        $newExercise->name = $name;
        return $newExercise;
    }

    public function withDescription(string $description) :Exercise {
        $newExercise = clone $this;
        $newExercise->description = $description;
        return $newExercise;
    }
}