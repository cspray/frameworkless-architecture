<?php declare(strict_types=1);


namespace Cspray\ArchDemo\Test\Entity;

use Cspray\ArchDemo\Entity\Dog;
use PHPUnit\Framework\TestCase;

class DogTest extends TestCase {

    /**
     * @var Dog
     */
    private $subject;

    public function setUp() {
        parent::setUp();
        $this->subject = new Dog();
    }

    public function testWithName() {
        $dog = $this->subject->withName('Nick');

        $this->assertNull($this->subject->getName());
        $this->assertSame('Nick', $dog->getName());
    }

    public function testWithBreed() {
        $dog = $this->subject->withBreed('Labrador Retriever');

        $this->assertNull($this->subject->getBreed());
        $this->assertSame('Labrador Retriever', $dog->getBreed());
    }

    public function testWithIncrementedAge() {
        $dog = $this->subject->withIncrementedAge(1)->withIncrementedAge(1)->withIncrementedAge(1);

        $this->assertSame(0, $this->subject->getAge());
        $this->assertSame(3, $dog->getAge());
    }

    public function testAllThreeTogether() {
        $dog = $this->subject->withName('Nick')->withBreed('Labrador Retriever')->withIncrementedAge(5);

        $this->assertSame('Nick', $dog->getName());
        $this->assertSame('Labrador Retriever', $dog->getBreed());
        $this->assertSame(5, $dog->getAge());
    }

}