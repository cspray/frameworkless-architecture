<?php declare(strict_types=1);


namespace Cspray\ArchDemo\Test\Model;

use function Cspray\ArchDemo\bootstrap;
use Cspray\ArchDemo\Entity\Dog;
use Cspray\ArchDemo\Entity\Entity;
use Cspray\ArchDemo\Model\DogModel;
use Cspray\ArchDemo\Test\Model\SharedExamples\CrudTest;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\DbUnit\Database\Connection;
use PHPUnit\DbUnit\DataSet\ArrayDataSet;
use PHPUnit\DbUnit\DataSet\IDataSet;
use PHPUnit\DbUnit\TestCase as DbTestCase;
use Ramsey\Uuid\Uuid;

class DogModelTest extends DbTestCase {

    use CrudTest;

    private $entityManager;
    /**
     * @var \Doctrine\DBAL\Connection
     */
    private $connection;

    private $nickId;
    private $kateId;
    private $chiefId;

    public function setUp(): void {
        // not great doing this but best way to make sure we get a valid instance of our doctrine entity manager
        $container = bootstrap('test');
        $this->entityManager = $container->make(EntityManagerInterface::class);
        $this->connection = $this->entityManager->getConnection();
        parent::setUp();
    }

    protected function subject() : object {
        return new DogModel($this->entityManager);
    }

    protected function tableName(): string {
        return 'dogs';
    }

    protected function validEntity(): Entity {
        return (new Dog())->withName('Ginapher')->withBreed('Boxer')->withIncrementedAge(6);
    }

    protected function invalidEntity() : Entity {
        return (new Dog())->withName('2385798375')->withBreed('Whatever')->withIncrementedAge(1);
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
        $this->nickId = Uuid::uuid4()->toString();
        $this->kateId = Uuid::uuid4()->toString();
        $this->chiefId = Uuid::uuid4()->toString();
        return new ArrayDataSet([
            'dogs' => [
                [
                    'id' => $this->nickId,
                    'name' => 'Nick',
                    'breed' => 'Labrador Retriever',
                    'age' => 5
                ],
                [
                    'id' => $this->kateId,
                    'name' => 'Kate',
                    'breed' => 'Pit Mix',
                    'age' => 4
                ],
                [
                    'id' => $this->chiefId,
                    'name' => 'Chief',
                    'breed' => 'Plott hound',
                    'age' => 3
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
        $dog = (new Dog())->withName($badName);

        $subject = new DogModel($this->entityManager);

        $this->assertFalse($subject->isValid($dog));
        $this->assertArraySubset(['name' => 'Must contain only letters, spaces and be between 3 and 50 characters long'], $subject->errors());
    }

    /**
     * @param string $badBreed
     * @dataProvider badStringProvider
     */
    public function testIsValidWithBadBreed(string $badBreed) {
        $dog = (new Dog())->withBreed($badBreed);

        $subject = new DogModel($this->entityManager);

        $this->assertFalse($subject->isValid($dog));
        $this->assertArraySubset(['breed' => 'Must contain only letters, spaces and be between 3 and 50 characters long'], $subject->errors());
    }

    public function badAgeProvider() {
        return [
            [-1],
            [0],
            [50],
            [0.5]
        ];
    }

    /**
     * @param int $age
     * @dataProvider badAgeProvider
     */
    public function testIsValidWithBadAge(int $age) {
        $dog = (new Dog())->withIncrementedAge($age);

        $subject = new DogModel($this->entityManager);

        $this->assertFalse($subject->isValid($dog));
        $this->assertArraySubset(['age' => 'Must be a positive number greater than 0 and less than or equal to 50'], $subject->errors());
    }

    public function testIsValidWithGoodData() {
        $dog = (new Dog())->withName('Missy')->withBreed('Chihuahua')->withIncrementedAge(13);

        $subject = new DogModel($this->entityManager);

        $this->assertTrue($subject->isValid($dog));
        $this->assertEmpty($subject->errors());
    }

}