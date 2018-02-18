<?php declare(strict_types=1);


namespace Cspray\ArchDemo\Test\Model;

use function Cspray\ArchDemo\bootstrap;
use Cspray\ArchDemo\Config\Environment;
use Cspray\ArchDemo\Entity\Entity;
use Cspray\ArchDemo\Entity\Trainer;
use Cspray\ArchDemo\Exception\NotFoundException;
use Cspray\ArchDemo\Model\TrainerModel;
use Cspray\ArchDemo\ObjectGraph;
use Cspray\ArchDemo\Test\Model\SharedExamples\CrudTest;
use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\DbUnit\Database\Connection;
use PHPUnit\DbUnit\DataSet\ArrayDataSet;
use PHPUnit\DbUnit\DataSet\IDataSet;
use PHPUnit\DbUnit\TestCase as DbTestCase;
use Ramsey\Uuid\Uuid;

class TrainerModelTest extends DbTestCase {

    use CrudTest;

    private $entityManager;
    /**
     * @var \Doctrine\DBAL\Connection
     */
    private $connection;

    private $charlesId;
    private $sarahId;
    private $chrisId;

    public function setUp(): void {
        // not great doing this but best way to make sure we get a valid instance of our doctrine entity manager
        $container = bootstrap('test');
        $this->entityManager = $container->make(EntityManagerInterface::class);
        $this->connection = $this->entityManager->getConnection();
        parent::setUp();
    }

    protected function subject() : object {
        return new TrainerModel($this->entityManager);
    }

    protected function tableName(): string {
        return 'trainers';
    }

    protected function validEntity(): Entity {
        return (new Trainer())->withName('Dyana')->withSpecialty('Clicker Training');
    }

    protected function invalidEntity(): Entity {
        return (new Trainer())->withName('2385798375')->withSpecialty('Whatever');
    }

    /**
     * Returns the test database connection.
     *
     * @return Connection
     */
    protected function getConnection() {
        return $this->createDefaultDBConnection($this->connection->getWrappedConnection(), 'archdemo_test');
    }

    /**
     * Returns the test dataset.
     *
     * @return IDataSet
     */
    protected function getDataSet() {
        $this->charlesId = Uuid::uuid4()->toString();
        $this->sarahId = Uuid::uuid4()->toString();
        $this->chrisId = Uuid::uuid4()->toString();
        return new ArrayDataSet([
            'trainers' => [
                [
                    'id' => $this->charlesId,
                    'name' => 'Charles',
                    'specialty' => 'Obedience'
                ],
                [
                    'id' => $this->sarahId,
                    'name' => 'Sarah',
                    'specialty' => 'Herding'
                ],
                [
                    'id' => $this->chrisId,
                    'name' => 'Christopher',
                    'specialty' => 'Agility'
                ]
            ]
        ]);
    }

    // Validation tests
    public function badStringProvider() {
        return [
            [''],
            ['a'],
            ['123'],
            ['$&^*%&'],
            [str_repeat('a', 51)]
        ];
    }

    /**
     * @param string $badName
     * @dataProvider badStringProvider
     */
    public function testIsValidWithBadNames(string $badName) {
        $Trainer = (new Trainer())->withName($badName);

        $subject = new TrainerModel($this->entityManager);

        $this->assertFalse($subject->isValid($Trainer));
        $this->assertArraySubset(['name' => 'Must contain only letters, spaces and be between 3 and 50 characters long'], $subject->errors());
    }

    /**
     * @param string $badBreed
     * @dataProvider badStringProvider
     */
    public function testIsValidWithBadSpecialty(string $badBreed) {
        $Trainer = (new Trainer())->withSpecialty($badBreed);

        $subject = new TrainerModel($this->entityManager);

        $this->assertFalse($subject->isValid($Trainer));
        $this->assertArraySubset(['specialty' => 'Must contain only letters, spaces and be between 3 and 50 characters long'], $subject->errors());
    }

    public function testIsValidWithGoodData() {
        $dog = (new Trainer())->withName('Missy')->withSpecialty('Small Dogs');

        $subject = new TrainerModel($this->entityManager);

        $this->assertTrue($subject->isValid($dog));
        $this->assertEmpty($subject->errors());
    }

}