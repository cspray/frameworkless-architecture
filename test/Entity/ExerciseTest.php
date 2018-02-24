<?php declare(strict_types=1);

namespace Cspray\ArchDemo\Test\Entity;

use Cspray\ArchDemo\Entity\Exercise;
use Cspray\ArchDemo\Middleware\ParsedRequestBodyMiddleware;
use PHPUnit\Framework\TestCase;

class ExerciseTest extends TestCase {
    /**
     * @var Exercise
     */
    private $subject;

    public function setUp() {
        parent::setUp();
        $this->subject = new Exercise('', '');
    }

    public function testWithName() {
        $exercise = $this->subject->withName('Feeding');

        $this->assertEmpty($this->subject->getName());
        $this->assertSame('Feeding', $exercise->getName());
    }

    public function testWithDescription() {
        $exercise = $this->subject->withDescription('Feed your pooch 1.5 cups of dry food mixed with 1/2 cup of wet food and 1 spoonful of greek yogurt.');

        $this->assertEmpty($this->subject->getDescription());
        $this->assertSame('Feed your pooch 1.5 cups of dry food mixed with 1/2 cup of wet food and 1 spoonful of greek yogurt.', $exercise->getDescription());
    }

    public function testAllTogether() {
        $exercise = $this->subject->withName('Heel')->withDescription('Have your dog walk calmly at your side.');

        $this->assertSame('Heel', $exercise->getName());
        $this->assertSame('Have your dog walk calmly at your side.', $exercise->getDescription());
    }

    public function testValidExercise() {
        $exercise = new Exercise('My Exercise', 'The description of my exercise. This can be up to 500 characters long and should be an informative paragraph.');
        $results = $exercise->validate();

        $this->assertTrue($results->isValid());
    }

    public function invalidExerciseNameProvider() {
        return [
            ['', 'name must have a length between 3 and 50'],
            ['a', 'name must have a length between 3 and 50'],
            [str_repeat('x', 100), 'name must have a length between 3 and 50'],
            ['*(^%#&*(^#%', 'name may only contain letters and spaces']
        ];
    }

    /**
     * @dataProvider invalidExerciseNameProvider
     */
    public function testInvalidExerciseName(string $actualName, string $expectedError) {
        $exercise = new Exercise($actualName, 'A description');
        $results = $exercise->validate();

        $this->assertFalse($results->isValid());
        $expected = [
            'name' => [
                $expectedError
            ]
        ];
        $this->assertSame($expected, $results->getErrorMessages());
    }

    public function invalidExerciseDescriptionProvider() {
        return [
            ['', 'description must have a length between 10 and 500'],
            [str_repeat('x', 600), 'description must have a length between 10 and 500']
        ];
    }

    /**
     * @dataProvider invalidExerciseDescriptionProvider
     */
    public function testInvalidExerciseDescription(string $actualDescription, string $expectedError) {
        $exercise = new Exercise('Dux', $actualDescription);
        $results = $exercise->validate();

        $this->assertFalse($results->isValid());
        $expected = [
            'description' => [
                $expectedError
            ]
        ];
        $this->assertSame($expected, $results->getErrorMessages());
    }

}