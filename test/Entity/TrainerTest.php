<?php declare(strict_types=1);


namespace Cspray\ArchDemo\Test\Entity;

use Cspray\ArchDemo\Entity\Trainer;
use PHPUnit\Framework\TestCase;

class TrainerTest extends TestCase
{

    /**
     * @var Trainer
     */
    private $subject;

    public function setUp()
    {
        parent::setUp();
        $this->subject = new Trainer('', '');
    }

    public function testWithName()
    {
        $trainer = $this->subject->withName('Nick');

        $this->assertEmpty($this->subject->getName());
        $this->assertSame('Nick', $trainer->getName());
    }

    public function testWithSpecialty()
    {
        $trainer = $this->subject->withSpecialty('Protection');

        $this->assertEmpty($this->subject->getSpecialty());
        $this->assertSame('Protection', $trainer->getSpecialty());
    }


    public function testAllTogether()
    {
        $trainer = $this->subject->withName('Nick')->withSpecialty('Agility');

        $this->assertSame('Nick', $trainer->getName());
        $this->assertSame('Agility', $trainer->getSpecialty());
    }

    public function testValidTrainer()
    {
        $exercise = new Trainer('Br Christopher', 'Obedience training and dog-human relationship.');
        $results = $exercise->validate();

        $this->assertTrue($results->isValid());
    }

    public function invalidTrainerNameProvider()
    {
        return [
            ['', 'name must have a length between 3 and 50'],
            ['a', 'name must have a length between 3 and 50'],
            [str_repeat('x', 100), 'name must have a length between 3 and 50'],
            ['*(^%#&*(^#%', 'name may only contain letters and spaces']
        ];
    }

    /**
     * @dataProvider invalidTrainerNameProvider
     */
    public function testInvalidTrainerName(string $actualName, string $expectedError)
    {
        $trainer = new Trainer($actualName, 'A description of their specialty');
        $results = $trainer->validate();

        $this->assertFalse($results->isValid());
        $expected = [
            'name' => [
                $expectedError
            ]
        ];
        $this->assertSame($expected, $results->getErrorMessages());
    }

    public function invalidTrainerSpecialtyProvider()
    {
        return [
            ['', 'specialty must have a length between 5 and 500'],
            [str_repeat('x', 600), 'specialty must have a length between 5 and 500']
        ];
    }

    /**
     * @dataProvider invalidTrainerSpecialtyProvider
     */
    public function testInvalidTrainerSpecialty(string $actualSpecialty, string $expectedError)
    {
        $trainer = new Trainer('Br Christopher', $actualSpecialty);
        $results = $trainer->validate();

        $this->assertFalse($results->isValid());
        $expected = [
            'specialty' => [
                $expectedError
            ]
        ];
        $this->assertSame($expected, $results->getErrorMessages());
    }
}
