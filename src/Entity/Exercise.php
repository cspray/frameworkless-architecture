<?php declare(strict_types=1);

namespace Cspray\ArchDemo\Entity;

use Cspray\ArchDemo\Validation\RuleSet as ValidationRuleset;
use Cspray\ArchDemo\Validation\Rule as ValidationRule;
use Cspray\ArchDemo\Validation\StdLib\RuleSet as StdLibRuleSet;
use Cspray\ArchDemo\Validation\ValidatableTrait;
use Cspray\ArchDemo\Validation\ZendValidator\RuleHelper as ZendRuleHelper;
use Ramsey\Uuid\Uuid;
use Zend\Validator as ZendValidator;

class Exercise implements Entity
{

    use ValidatableTrait;
    use ZendRuleHelper;

    private $id;
    private $name;
    private $description;

    public function __construct(string $name, string $description)
    {
        $this->id = Uuid::uuid4();
        $this->name = $name;
        $this->description = $description;
    }

    protected function validationRuleSet(): ValidationRuleSet
    {
        return new StdLibRuleSet([
            'name' => $this->createNameRule(),
            'description' => $this->createDescriiptionRule()
        ]);
    }

    private function createNameRule() : ValidationRule
    {
        $doBreakChain = true;
        $chain = new ZendValidator\ValidatorChain();
        $stringLength = new ZendValidator\StringLength(['min' => 3, 'max' => 50]);
        $stringLength->setMessage('name must have a length between %min% and %max%');

        $alphaWithSpaces = new ZendValidator\Regex('/[A-Za-z ]/');
        $alphaWithSpaces->setMessage('name may only contain letters and spaces');

        $chain->attach($stringLength, $doBreakChain)->attach($alphaWithSpaces);

        return $this->createRuleForZendValidator($chain);
    }

    private function createDescriiptionRule() : ValidationRule
    {
        $stringLength = new ZendValidator\StringLength(['min' => 10, 'max' => 500]);
        $stringLength->setMessage('description must have a length between %min% and %max%');

        return $this->createRuleForZendValidator($stringLength);
    }

    public function getId()
    {
        return $this->id;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function withName(string $name) : Exercise
    {
        $newExercise = clone $this;
        $newExercise->name = $name;
        return $newExercise;
    }

    public function withDescription(string $description) :Exercise
    {
        $newExercise = clone $this;
        $newExercise->description = $description;
        return $newExercise;
    }
}
