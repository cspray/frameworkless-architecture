<?php declare(strict_types=1);

namespace Cspray\ArchDemo\Test\Repository;

use function Cspray\ArchDemo\bootstrap;
use Cspray\ArchDemo\Entity\Dog;
use Cspray\ArchDemo\Entity\Entity;
use Cspray\ArchDemo\Entity\Exercise;
use Cspray\ArchDemo\Repository\ExerciseRepository;
use Cspray\ArchDemo\Test\Repository\SharedExamples\CrudTest;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\DbUnit\Database\Connection;
use PHPUnit\DbUnit\DataSet\ArrayDataSet;
use PHPUnit\DbUnit\DataSet\IDataSet;
use PHPUnit\DbUnit\TestCase as DbTestCase;
use Ramsey\Uuid\Uuid;

class ExerciseRepositoryTest extends DbTestCase
{

    use CrudTest;

    private $entityManager;
    /**
     * @var \Doctrine\DBAL\Connection
     */
    private $connection;

    private $obedienceId;
    private $feedingId;
    private $agilityId;

    public function setUp(): void
    {
        // not great doing this but best way to make sure we get a valid instance of our doctrine entity manager
        $container = bootstrap('test');
        $this->entityManager = $container->make(EntityManagerInterface::class);
        $this->connection = $this->entityManager->getConnection();
        parent::setUp();
    }

    protected function subject(): object
    {
        return new ExerciseRepository($this->entityManager);
    }

    protected function tableName(): string
    {
        return 'exercises';
    }

    protected function entityClass(): string
    {
        return Exercise::class;
    }

    protected function validEntity() : Entity
    {
        return new Exercise('Clicker', 'Use sounds to positively reinforce your dog');
    }

    protected function invalidEntity(): Entity
    {
        return new Exercise('', '');
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
        $this->obedienceId = Uuid::uuid4()->toString();
        $this->feedingId = Uuid::uuid4()->toString();
        $this->agilityId = Uuid::uuid4()->toString();
        $feediingDescription = 'Feeding is a training opportunity!';
        $feediingDescription .= '.5 cups of dry food, .5 cup of wet and a spoon of greek yogurt.';
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
                    'description' => $feediingDescription
                ],
                [
                    'id' => $this->agilityId,
                    'name' => 'Agility',
                    'description' => 'Stimulate your dog\'s brains and brawn with this challenging obstacle course.'
                ]
            ]
        ]);
    }
}
