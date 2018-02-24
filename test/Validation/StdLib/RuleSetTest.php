<?php declare(strict_types=1);

namespace Cspray\ArchDemo\Test\Validation\StdLib;

use Cspray\ArchDemo\Exception\InvalidTypeException;
use Cspray\ArchDemo\Validation\Rule;
use Cspray\ArchDemo\Validation\StdLib\RuleSet;
use PHPUnit\Framework\TestCase;

class RuleSetTest extends TestCase {

    public function testRuleForAttributeNotPresent() {
        $subject = new RuleSet([]);
        $rule = $subject->ruleForAttribute('foo');
        $results = $rule->passesCheck('whatever');
        $this->assertTrue($results->isValid());
        $this->assertEmpty($results->getErrorMessages());
    }

    public function testRuleForAttributePresent() {
        $subject = new RuleSet([
            'foo' => $actual = $this->getMockBuilder(Rule::class)->getMock()
        ]);
        $rule = $subject->ruleForAttribute('foo');
        $this->assertSame($rule, $actual);
    }

    public function testThrowExceptionIfValueInRulesIsNotRule() {
        $this->expectException(InvalidTypeException::class);
        $this->expectExceptionMessage('All values passed must be a ' . Rule::class);
        new RuleSet(['not a rule']);
    }

    public function testIteratingOverRuleSet() {
        $subject = new RuleSet($expected = [
            'a' => $this->getMockBuilder(Rule::class)->getMock(),
            'b' => $this->getMockBuilder(Rule::class)->getMock(),
            'c' => $this->getMockBuilder(Rule::class)->getMock()
        ]);
        $this->assertSame($expected, iterator_to_array($subject->getIterator()));
    }

}