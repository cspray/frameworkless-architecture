<?php declare(strict_types=1);

namespace Cspray\ArchDemo\Test\Repository\SharedExamples;

use Cspray\ArchDemo\Entity\Entity;
use Cspray\ArchDemo\Exception\InvalidTypeException;
use Cspray\ArchDemo\Exception\NotFoundException;
use PHPUnit\DbUnit\DataSet\ArrayDataSet;
use Ramsey\Uuid\Uuid;

trait CrudTest {

    abstract protected function subject() : object;

    abstract protected function tableName() : string;

    abstract protected function entityClass() : string;

    abstract protected function validEntity() : Entity;

    abstract protected function invalidEntity() : Entity;

    abstract protected function wrongTypeEntity() : Entity;

    public function testAllReturnsEveryAvailableEntity() {
        $subject = $this->subject();
        $entities = $subject->findAll();
        $ids = [$this->tableName() => []];

        foreach ($entities as $entity) {
            $this->assertInstanceOf($this->entityClass(), $entity);
            $ids[$this->tableName()][] = ['id' => (string) $entity->getId()];
        }

        $expectedDataSet = (new ArrayDataSet($ids))->getTable($this->tableName());
        $actualDataSet = $this->getConnection()->createQueryTable(
            $this->tableName(), 'SELECT id FROM ' . $this->tableName()
        );

        $this->assertTablesEqual($expectedDataSet, $actualDataSet);
    }

    public function testFindEntity() {
        $subject = $this->subject();
        $table = $this->getConnection()->createQueryTable(
            $this->tableName(), 'SELECT id FROM ' . $this->tableName()
        );
        $id = $table->getValue(0, 'id');

        $this->assertNotNull($id, 'We did not find any entities stored in this test fixture');

        $entity = $subject->find(Uuid::fromString($id));
        $this->assertSame($id, (string) $entity->getId());
    }

    public function testFindEntityNotFound() {
        $subject = $this->subject();
        $randomId = Uuid::uuid4();
        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessage("Could not find a {$this->entityClass()} with ID {$randomId}");
        $subject->find($randomId);
    }

    public function testSaveValidEntity() {
        $subject = $this->subject();
        $originalRowCount = $this->getConnection()->getRowCount($this->tableName());
        $this->assertTrue($subject->save($this->validEntity()));
        $this->assertSame($originalRowCount + 1, $this->getConnection()->getRowCount($this->tableName()));
    }

    public function testSaveInvalidEntity() {
        $subject = $this->subject();
        $originalRowCount = $this->getConnection()->getRowCount($this->tableName());
        $this->assertFalse($subject->save($this->invalidEntity()));
        $this->assertSame($originalRowCount, $this->getConnection()->getRowCount($this->tableName()));
    }

    public function testSaveWrongTypeEntity() {
        $subject = $this->subject();
        $entity = $this->wrongTypeEntity();

        $this->expectException(InvalidTypeException::class);
        $this->expectExceptionMessage('Expected an entity with type ' . $this->entityClass() . ' but got ' . get_class($entity));

        $subject->save($entity);
    }

    public function testDeleteEntityFound() {
        $subject = $this->subject();
        $originalRowCount = $this->getConnection()->getRowCount($this->tableName());
        $table = $this->getConnection()->createQueryTable(
            $this->tableName(), 'SELECT id FROM ' . $this->tableName()
        );
        $id = $table->getValue(0, 'id');

        $subject->delete(Uuid::fromString($id));

        $this->assertSame($originalRowCount - 1, $this->getConnection()->getRowCount($this->tableName()));
    }

    public function testDeleteEntityNotFound() {
        $subject = $this->subject();
        $originalRowCount = $this->getConnection()->getRowCount($this->tableName());
        $subject->delete(Uuid::uuid4());

        $this->assertSame($originalRowCount, $this->getConnection()->getRowCount($this->tableName()));
    }

}