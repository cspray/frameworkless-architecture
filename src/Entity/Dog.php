<?php declare(strict_types=1);

namespace Cspray\ArchDemo\Entity;

use Cspray\ArchDemo\Validation\RuleSet as ValidationRuleSet;
use Cspray\ArchDemo\Validation\Rule as ValidationRule;
use Cspray\ArchDemo\Validation\StdLib\RuleSet as StdLibRuleSet;
use Cspray\ArchDemo\Validation\ValidatableTrait;
use Cspray\ArchDemo\Validation\ZendValidator\RuleHelper as ZendRuleHelper;
use Ramsey\Uuid\Uuid;
use Zend\Validator as ZendValidator;

class Dog implements Entity {

    use ValidatableTrait;
    use ZendRuleHelper;

    private $id;
    private $name;
    private $breed;
    private $age = 0;

    public function __construct(string $name, string $breed, int $ageInYears) {
        $this->id = Uuid::uuid4();
        $this->name = $name;
        $this->breed = $breed;
        $this->age = $ageInYears;
    }

    protected function validationRuleSet(): ValidationRuleSet {
        return new StdLibRuleSet([
            'name' => $this->createNameRule(),
            'breed' => $this->createBreedRule(),
            'age' => $this->createAgeRule()
        ]);
    }

    private function createNameRule() : ValidationRule {
        return $this->createBadStringRule('name');
    }

    private function createBreedRule() : ValidationRule {
        return $this->createBadStringRule('breed');
    }

    private function createAgeRule() : ValidationRule {
        $chain = new ZendValidator\ValidatorChain();
        $greaterThan = new ZendValidator\GreaterThan(0);
        $greaterThan->setMessage('age must be greater than %min%');

        $lessThan = new ZendValidator\LessThan(50);
        $lessThan->setMessage('age must be less than %max%');

        $chain->attach($greaterThan)->attach($lessThan);

        return $this->createRuleForZendValidator($chain);
    }

    private function createBadStringRule(string $attribute) : ValidationRule {
        $doBreakChain = true;
        $chain = new ZendValidator\ValidatorChain();
        $stringLength = new ZendValidator\StringLength(['min' => 3, 'max' => 50]);
        $stringLength->setMessage($attribute . ' must have a length between %min% and %max%');

        $alphaWithSpaces = new ZendValidator\Regex('/[A-Za-z ]/');
        $alphaWithSpaces->setMessage($attribute . ' may only contain letters and spaces');

        $chain->attach($stringLength, $doBreakChain)->attach($alphaWithSpaces);

        return $this->createRuleForZendValidator($chain);
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

    public function withAgedOneYear() : Dog {
        $newDog = clone $this;
        $newDog->age++;
        return $newDog;
    }

}