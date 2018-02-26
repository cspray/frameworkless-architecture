<?php declare(strict_types=1);

namespace Cspray\ArchDemo\Validation\ZendValidator;

use Zend\Validator as ZendValidator;
use Cspray\ArchDemo\Validation\Rule;
use Cspray\ArchDemo\Validation\Results;

trait RuleHelper {

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

}