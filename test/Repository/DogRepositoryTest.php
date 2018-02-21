<?php declare(strict_types=1);


namespace Cspray\ArchDemo\Test\Repository;

use function Cspray\ArchDemo\bootstrap;
use Cspray\ArchDemo\Entity\Dog;
use Cspray\ArchDemo\Entity\Entity;
use Cspray\ArchDemo\Entity\Trainer;
use Cspray\ArchDemo\Repository\DogRepository;
use Cspray\ArchDemo\Test\Repository\SharedExamples\CrudTest;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\DbUnit\Database\Connection;
use PHPUnit\DbUnit\DataSet\ArrayDataSet;
use PHPUnit\DbUnit\DataSet\IDataSet;
use PHPUnit\DbUnit\TestCase as DbTestCase;
use Ramsey\Uuid\Uuid;

class DogRepositoryTest extends DbTestCase {

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
        return new DogRepository($this->entityManager);
    }

    protected function tableName(): string {
        return 'dogs';
    }

    protected function entityClass() : string {
        return Dog::class;
    }

    protected function validEntity(): Entity {
        return (new Dog())->withName('Ginapher')->withBreed('Boxer')->withIncrementedAge(6);
    }

    protected function invalidEntity() : Entity {
        return (new Dog())->withName('2385798375')->withBreed('Whatever')->withIncrementedAge(1);
    }

    protected function wrongTypeEntity(): Entity {
        return new Trainer();
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

}