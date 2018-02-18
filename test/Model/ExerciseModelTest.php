<?php declare(strict_types=1);

namespace Cspray\ArchDemo\Test\Model;

use function Cspray\ArchDemo\bootstrap;
use Cspray\ArchDemo\Entity\Entity;
use Cspray\ArchDemo\Entity\Exercise;
use Cspray\ArchDemo\Exception\NotFoundException;
use Cspray\ArchDemo\Model\ExerciseModel;
use Cspray\ArchDemo\Test\Model\SharedExamples\CrudTest;
use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\DbUnit\Database\Connection;
use PHPUnit\DbUnit\DataSet\ArrayDataSet;
use PHPUnit\DbUnit\DataSet\IDataSet;
use PHPUnit\DbUnit\TestCase as DbTestCase;
use Ramsey\Uuid\Uuid;

class ExerciseModelTest extends DbTestCase {

    use CrudTest;

    private $entityManager;
    /**
     * @var \Doctrine\DBAL\Connection
     */
    private $connection;

    private $obedienceId;
    private $feedingId;
    private $agilityId;

    public function setUp(): void {
        // not great doing this but best way to make sure we get a valid instance of our doctrine entity manager
        $container = bootstrap('test');
        $this->entityManager = $container->make(EntityManagerInterface::class);
        $this->connection = $this->entityManager->getConnection();
        parent::setUp();
    }

    protected function subject(): object {
        return new ExerciseModel($this->entityManager);
    }

    protected function tableName(): string {
        return 'exercises';
    }

    protected function validEntity() : Entity {
        return (new Exercise())->withName('Clicker')->withDescription('Use sounds to positively reinforce your dog');
    }

    protected function invalidEntity(): Entity {
        return new Exercise();
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
        $this->obedienceId = Uuid::uuid4()->toString();
        $this->feedingId = Uuid::uuid4()->toString();
        $this->agilityId = Uuid::uuid4()->toString();
        return new ArrayDataSet([
            'exercises' => [
                [
                    'id' => $this->obedienceId,
                    'name' => 'Obedience',
                    'description' => 'Go over the obedience basics: sit, down, stay, come, and heel.'
                ],
                [
                    'id' => $this->feedingId,
                    'name' => 'Feeding',
                    'description' => 'Feeding is a training opportunity! Large dogs should get 1.5 cups of dry food, .5 cup of wet and a big spoot of greek yogurt.'
                ],
                [
                    'id' => $this->agilityId,
                    'name' => 'Agility',
                    'description' => 'Stimulate your dog\'s brains and brawn with this challenging obstacle course.'
                ]
            ]
        ]);
    }

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
        $exercise = (new Exercise())->withName($badName);
        $subject = $this->subject();
        $this->assertFalse($subject->isValid($exercise));
        $this->assertArraySubset(['name' => 'Must contain only letters, spaces and be between 3 and 50 characters long'], $subject->errors());
    }

    public function badDescriptionProvider() {
        return [
            [''],
            ['a'],
            [123],
            [str_repeat('a', 10000)]
        ];
    }

    /**
     * @param string $badDescription
     * @dataProvider badDescriptionProvider
     */
    public function testIsValidWithBadDescriptions(string $badDescription) {
        $exercise = (new Exercise())->withDescription($badDescription);
        $subject = $this->subject();
        $this->assertFalse($subject->isValid($exercise));
        $this->assertArraySubset(['description' => 'Must be greater than 3 and fewer than 1,000 characters long'], $subject->errors());
    }


}