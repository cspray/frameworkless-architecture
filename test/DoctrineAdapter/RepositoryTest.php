<?php declare(strict_types=1);

namespace Cspray\ArchDemo\Test\DoctrineAdapter;

use function Cspray\ArchDemo\bootstrap;
use Cspray\ArchDemo\DoctrineAdapter\DogRepository;
use Cspray\ArchDemo\DoctrineAdapter\ExerciseRepository;
use Cspray\ArchDemo\DoctrineAdapter\TrainerRepository;
use Cspray\ArchDemo\Entity\Dog;
use Cspray\ArchDemo\Entity\Exercise;
use Cspray\ArchDemo\Entity\Trainer;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

class RepositoryTest extends TestCase
{

    private $entityManager;

    public function setUp()/* The :void return type declaration that should be here would cause a BC issue */
    {
        parent::setUp();
        $injector = bootstrap('test');
        $this->entityManager = $injector->make(EntityManagerInterface::class);
    }

    public function entityRepositoryProvider()
    {
        return [
            [Dog::class, DogRepository::class],
            [Exercise::class, ExerciseRepository::class],
            [Trainer::class, TrainerRepository::class]
        ];
    }

    /**
     * @dataProvider entityRepositoryProvider
     */
    public function testEntityManagerRepositoryCorrectType(string $entityClass, string $expectedClass)
    {
        $actual = $this->entityManager->getRepository($entityClass);

        $this->assertInstanceOf($expectedClass, $actual);
    }
}
