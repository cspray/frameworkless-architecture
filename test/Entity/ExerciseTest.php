<?php declare(strict_types=1);

namespace Cspray\ArchDemo\Test\Entity;

use Cspray\ArchDemo\Entity\Exercise;
use PHPUnit\Framework\TestCase;

class ExerciseTest extends TestCase {
    /**
     * @var Exercise
     */
    private $subject;

    public function setUp() {
        parent::setUp();
        $this->subject = new Exercise();
    }

    public function testWithName() {
        $trainer = $this->subject->withName('Feeding');

        $this->assertNull($this->subject->getName());
        $this->assertSame('Feeding', $trainer->getName());
    }

    public function testWithDescription() {
        $trainer = $this->subject->withDescription('Feed your pooch 1.5 cups of dry food mixed with 1/2 cup of wet food and 1 spoonful of greek yogurt.');

        $this->assertNull($this->subject->getDescription());
        $this->assertSame('Feed your pooch 1.5 cups of dry food mixed with 1/2 cup of wet food and 1 spoonful of greek yogurt.', $trainer->getDescription());
    }


    public function testAllTogether() {
        $trainer = $this->subject->withName('Heel')->withDescription('Have your dog walk calmly at your side.');

        $this->assertSame('Heel', $trainer->getName());
        $this->assertSame('Have your dog walk calmly at your side.', $trainer->getDescription());
    }
}