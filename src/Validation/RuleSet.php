<?php declare(strict_types=1);

namespace Cspray\ArchDemo\Validation;

use Traversable;

interface RuleSet extends Traversable {

    public function ruleForAttribute(string $attribute) : Rule;

}