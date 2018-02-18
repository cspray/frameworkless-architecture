<?php declare(strict_types=1);


namespace Cspray\ArchDemo\Test;

use function Cspray\ArchDemo\bootstrap;
use Doctrine\DBAL\Types\Type;
use PHPUnit\Framework\TestCase;

class BootstrapFunctionTest extends TestCase {

    public function testBootstrapAddsDoctrineUuidType() {
        bootstrap('test');
        $this->assertTrue(Type::hasType('uuid'));
    }

    public function testBootstrapAddsDoctrineUuidBinaryType() {
        bootstrap('test');
        $this->assertTrue(Type::hasType('uuid_binary'));
    }

    public function testBootstrapHandlesMultipleCallsToDifferentEnvironment() {
        $injector1 = bootstrap('test');
        $injector2 = bootstrap('development');

        $this->assertNotSame($injector1, $injector2);
    }

    public function testBootstrapHandlesMultipleCallsToSameEnvironment() {
        $injector1 = bootstrap('test');
        $injector2 = bootstrap('test');

        $this->assertSame($injector1, $injector2);
    }

}