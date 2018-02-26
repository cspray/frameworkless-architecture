<?php declare(strict_types=1);


namespace Cspray\ArchDemo\Test\Repository;

use function Cspray\ArchDemo\bootstrap;
use Cspray\ArchDemo\Entity\Dog;
use Cspray\ArchDemo\Entity\Entity;
use Cspray\ArchDemo\Entity\Trainer;
use Cspray\ArchDemo\Repository\TrainerRepository;
use Cspray\ArchDemo\Test\Repository\SharedExamples\CrudTest;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\DbUnit\Database\Connection;
use PHPUnit\DbUnit\DataSet\ArrayDataSet;
use PHPUnit\DbUnit\DataSet\IDataSet;
use PHPUnit\DbUnit\TestCase as DbTestCase;
use Ramsey\Uuid\Uuid;

class TrainerRepositoryTest extends DbTestCase
{

    use CrudTest;

    private $entityManager;
    /**
     * @var \Doctrine\DBAL\Connection
     */
    private $connection;

    private $charlesId;
    private $sarahId;
    private $chrisId;

    public function setUp(): void
    {
        // not great doing this but best way to make sure we get a valid instance of our doctrine entity manager
        $container = bootstrap('test');
        $this->entityManager = $container->make(EntityManagerInterface::class);
        $this->connection = $this->entityManager->getConnection();
        parent::setUp();
    }

    protected function subject() : object
    {
        return new TrainerRepository($this->entityManager);
    }

    protected function tableName(): string
    {
        return 'trainers';
    }

    protected function entityClass(): string
    {
        return Trainer::class;
    }

    protected function validEntity(): Entity
    {
        return new Trainer('Dyana', 'Clicker training');
    }

    protected function invalidEntity(): Entity
    {
        return new Trainer('2385798375', 'Whatever');
    }

    protected function wrongTypeEntity(): Entity
    {
        return new Dog('', '', 0);
    }

    /**
     * Returns the test database connection.
     *
     * @return Connection
     */
    protected function getConnection()
    {
        return $this->createDefaultDBConnection($this->connection->getWrappedConnection(), 'archdemo_test');
    }

    /**
     * Returns the test dataset.
     *
     * @return IDataSet
     */
    protected function getDataSet()
    {
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
}
