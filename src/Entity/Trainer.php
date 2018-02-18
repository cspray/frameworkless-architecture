<?php declare(strict_types=1);

namespace Cspray\ArchDemo\Entity;
use Ramsey\Uuid\Uuid;

class Trainer implements Entity {

    private $id;
    private $name;
    private $specialty;

    public function __construct() {
        $this->id = Uuid::uuid4();
    }

    public function getId() {
        return $this->id;
    }

    public function getName() {
        return $this->name;
    }

    public function getSpecialty() {
        return $this->specialty;
    }

    public function withName(string $name) : Trainer {
        $newTrainer = clone $this;
        $newTrainer->name = $name;
        return $newTrainer;
    }

    public function withSpecialty(string $specialty) : Trainer {
        $newTrainer = clone $this;
        $newTrainer->specialty = $specialty;
        return $newTrainer;
    }

}