<?php declare(strict_types=1);


namespace Cspray\ArchDemo\Validation;

use Zend\Validator as ZendValidator;

trait ValidatableTrait /* implements Validatable */ {

    public function validate() : Results {
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

            public function __construct(array $resultData) {
                $this->resultData = $resultData;
            }

            public function isValid(): bool {
                return empty($this->resultData);
            }

            public function getErrorMessages(): array {
                return $this->resultData;
            }
        };
    }

    protected function createRuleForZendValidator(ZendValidator\ValidatorInterface $validator) : Rule {
        return new class($validator) implements Rule {
            private $validator;

            public function __construct(ZendValidator\ValidatorInterface $validator) {
                $this->validator = $validator;
            }

            public function passesCheck($value, string $attributeName = null): Results {
                $passes = $this->validator->isValid($value);
                $errorMessages = $this->validator->getMessages();

                return new class($passes, $errorMessages) implements Results {
                    private $passes;
                    private $errorMessages;

                    public function __construct(bool $passes, array $errorMessages) {
                        $this->passes = $passes;
                        $this->errorMessages = $errorMessages;
                    }

                    public function isValid(): bool {
                        return $this->passes;
                    }

                    public function getErrorMessages(): array {
                        return array_values($this->errorMessages);
                    }
                };
            }
        };
    }

    abstract protected function validationRuleSet() : RuleSet;

}