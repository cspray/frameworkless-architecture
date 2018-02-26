<?php declare(strict_types=1);


namespace Cspray\ArchDemo\Validation;

use Zend\Validator as ZendValidator;

trait ValidatableTrait /* implements Validatable */
{

    public function validate() : Results
    {
        $rules = $this->validationRuleSet();
        $resultData = [];
        /** @var Rule $rule */
        foreach ($rules as $attr => $rule) {
            $getter = 'get' . ucfirst($attr);
            $value = $this->$getter();
            $results = $rule->passesCheck($value, $attr);
            if (!$results->isValid()) {
                $resultData[$attr] = $results->getErrorMessages();
            }
        }

        return new class($resultData) implements Results {
            private $resultData;

            public function __construct(array $resultData)
            {
                $this->resultData = $resultData;
            }

            public function isValid(): bool
            {
                return empty($this->resultData);
            }

            public function getErrorMessages(): array
            {
                return $this->resultData;
            }
        };
    }

    abstract protected function validationRuleSet() : RuleSet;
}
