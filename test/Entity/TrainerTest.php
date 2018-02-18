<?php declare(strict_types=1);


namespace Cspray\ArchDemo\Test\Entity;

use Cspray\ArchDemo\Entity\Trainer;
use PHPUnit\Framework\TestCase;

class TrainerTest extends TestCase {

    /**
     * @var Trainer
     */
    private $subject;

    public function setUp() {
        parent::setUp();
        $this->subject = new Trainer();
    }

    public function testWithName() {
        $trainer = $this->subject->withName('Nick');

        $this->assertNull($this->subject->getName());
        $this->assertSame('Nick', $trainer->getName());
    }

    public function testWithSpecialty() {
        $trainer = $this->subject->withSpecialty('Protection');

        $this->assertNull($this->subject->getSpecialty());
        $this->assertSame('Protection', $trainer->getSpecialty());
    }


    public function testAllTogether() {
        $trainer = $this->subject->withName('Nick')->withSpecialty('Agility');

        $this->assertSame('Nick', $trainer->getName());
        $this->assertSame('Agility', $trainer->getSpecialty());
    }

}