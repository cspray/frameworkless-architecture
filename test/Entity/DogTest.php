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
        $this->subject = new Dog('', '', 0);
    }

    public function testWithName() {
        $dog = $this->subject->withName('Nick');

        $this->assertEmpty($this->subject->getName());
        $this->assertSame('Nick', $dog->getName());
    }

    public function testWithIncrementedAge() {
        $dog = $this->subject->withAgedOneYear();

        $this->assertSame(0, $this->subject->getAge());
        $this->assertSame(1, $dog->getAge());
    }

    public function testAllThreeTogether() {
        $dog = (new Dog('Nick', 'Labrador Retriever', 0))->withAgedOneYear()->withAgedOneYear();

        $this->assertSame('Nick', $dog->getName());
        $this->assertSame('Labrador Retriever', $dog->getBreed());
        $this->assertSame(2, $dog->getAge());
    }

    public function testValidDog() {
        $dog = new Dog('Nick Sprayberry', 'Labrador Retriever', 1);
        $results = $dog->validate();
        $this->assertTrue($results->isValid());
    }

    private function badStringsForAttribute(string $attribute) {
        return [
            ['', $attribute . ' must have a length between 3 and 50'],
            ['a', $attribute . ' must have a length between 3 and 50'],
            [str_repeat('x', 100), $attribute . ' must have a length between 3 and 50'],
            ['*(^%#&*(^#%', $attribute . ' may only contain letters and spaces']
        ];
    }

    public function invalidDogNameProvider() {
        return $this->badStringsForAttribute('name');
    }

    /**
     * @dataProvider invalidDogNameProvider
     */
    public function testInvalidDogName(string $actualName, string $expectedError) {
        $dog = new Dog($actualName, 'Labrador Retriever', 1);
        $results = $dog->validate();
        $this->assertFalse($results->isValid());
        $expected = ['name' => [
            $expectedError
        ]];
        $this->assertSame($expected, $results->getErrorMessages());
    }

    public function invalidDogBreedProvider() {
        return $this->badStringsForAttribute('breed');
    }

    /**
     * @dataProvider invalidDogBreedProvider
     */
    public function testInvalidDogBreed(string $actualBreed, string $expectedError) {
        $dog = new Dog('Kate Sprayberry', $actualBreed, 1);
        $results = $dog->validate();
        $this->assertFalse($results->isValid());
        $expected = ['breed' => [
            $expectedError
        ]];
        $this->assertSame($expected, $results->getErrorMessages());
    }

    public function invalidDogAgeProvider() {
        return [
            [0, 'age must be greater than 0'],
            [-1, 'age must be greater than 0'],
            [50, 'age must be less than 50']
        ];
    }

    /**
     * @dataProvider invalidDogAgeProvider
     */
    public function testInvalidDogAge(int $actualAge, string $expectedError) {
        $dog = new Dog('Nick', 'Labrador Retriever', $actualAge);
        $results = $dog->validate();
        $this->assertFalse($results->isValid());
        $expected = ['age' => [
            $expectedError
        ]];
        $this->assertSame($expected, $results->getErrorMessages());
    }

}