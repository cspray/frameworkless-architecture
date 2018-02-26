<?php declare(strict_types=1);


namespace Cspray\ArchDemo\Validation\StdLib;

use Cspray\ArchDemo\Exception\InvalidTypeException;
use Cspray\ArchDemo\Validation\Results as ValidationResults;
use Cspray\ArchDemo\Validation\Rule as ValidationRule;
use Cspray\ArchDemo\Validation\RuleSet as ValidationRuleSet;
use ArrayObject;
use IteratorAggregate;

class RuleSet implements IteratorAggregate, ValidationRuleSet
{

    private $rules;

    /**
     * The passed array should be an associative array where the key represents the attribute and the value of that
     * attribute is a Cspray\ArchDemo\Validation\Rule.
     *
     * If a value in the $rules array does not implement the appropriate type an exception will be thrown by this
     * method.
     *
     * @param array $rules
     * @throws InvalidTypeException
     */
    public function __construct(array $rules)
    {
        foreach ($rules as $rule) {
            if (!$rule instanceof ValidationRule) {
                throw new InvalidTypeException('All values passed must be a ' . ValidationRule::class);
            }
        }
        $this->rules = $rules;
    }

    public function ruleForAttribute(string $attribute): ValidationRule
    {
        if (isset($this->rules[$attribute])) {
            return $this->rules[$attribute];
        }
        return new class implements ValidationRule {
            public function passesCheck($value, string $attributeName = null) : ValidationResults
            {
                return new class implements ValidationResults {
                    public function isValid(): bool
                    {
                        return true;
                    }

                    public function getErrorMessages(): array
                    {
                        return [];
                    }
                };
            }
        };
    }

    /**
     * Retrieve an external iterator
     * @link http://php.net/manual/en/iteratoraggregate.getiterator.php
     * @return \Traversable An instance of an object implementing <b>Iterator</b> or
     * <b>Traversable</b>
     * @since 5.0.0
     */
    public function getIterator()
    {
        return new ArrayObject($this->rules);
    }
}
