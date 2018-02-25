<?php declare(strict_types=1);

namespace Cspray\ArchDemo\Entity;

use Cspray\ArchDemo\Validation\RuleSet as ValidationRuleSet;
use Cspray\ArchDemo\Validation\Rule as ValidationRule;
use Cspray\ArchDemo\Validation\StdLib\RuleSet as StdLibRuleSet;
use Cspray\ArchDemo\Validation\ValidatableTrait;
use Ramsey\Uuid\Uuid;
use Zend\Validator as ZendValidator;

class Trainer implements Entity {

    use ValidatableTrait;

    private $id;
    private $name;
    private $specialty;

    public function __construct(string $name, string $specialty) {
        $this->id = Uuid::uuid4();
        $this->name = $name;
        $this->specialty = $specialty;
    }

    protected function validationRuleSet(): ValidationRuleSet {
        return new StdLibRuleSet([
            'name' => $this->createNameRule(),
            'specialty' => $this->createSpecialtyRule()
        ]);
    }
    private function createNameRule() : ValidationRule {
        $doBreakChain = true;
        $chain = new ZendValidator\ValidatorChain();
        $stringLength = new ZendValidator\StringLength(['min' => 3, 'max' => 50]);
        $stringLength->setMessage('name must have a length between %min% and %max%');

        $alphaWithSpaces = new ZendValidator\Regex('/[A-Za-z ]/');
        $alphaWithSpaces->setMessage('name may only contain letters and spaces');

        $chain->attach($stringLength, $doBreakChain)->attach($alphaWithSpaces);

        return $this->createRuleForZendValidator($chain);
    }

    private function createSpecialtyRule() : ValidationRule {
        $stringLength = new ZendValidator\StringLength(['min' => 5, 'max' => 500]);
        $stringLength->setMessage('specialty must have a length between %min% and %max%');

        return $this->createRuleForZendValidator($stringLength);
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