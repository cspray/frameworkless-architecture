<?php declare(strict_types=1);

namespace Cspray\ArchDemo\Test\Controller;

use Cspray\ArchDemo\Controller\DogController;
use Cspray\ArchDemo\Entity\Dog;
use Cspray\ArchDemo\Exception\NotFoundException;
use Cspray\ArchDemo\Repository\DogRepository;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use Zend\Diactoros\ServerRequest;
use ArrayObject;

class DogControllerTest extends TestCase {

    private function createDog(string $name, string $breed, int $age) : Dog {
        return new Dog($name, $breed, $age);
    }

    public function testIndex() {
        $dogs = [
            $this->createDog('Nick', 'Labrador Retriever', 5),
            $this->createDog('Kate', 'Pit Mix', 4),
            $this->createDog('Chief', 'Plott Hound', 3)
        ];
        $mockDogRepository = $this->getMockBuilder(DogRepository::class)->disableOriginalConstructor()->getMock();
        $mockDogRepository->expects($this->once())->method('findAll')->willReturn(new ArrayObject($dogs));
        $subject = new DogController($mockDogRepository, new \League\Fractal\Manager());

        $response = $subject->index(new ServerRequest());

        $expectedJson = [
            'data' => [
                [
                    'name' => 'Nick',
                    'breed' => 'Labrador Retriever',
                    'age' => 5
                ],
                [
                    'name' => 'Kate',
                    'breed' => 'Pit Mix',
                    'age' => 4
                ],
                [
                    'name' => 'Chief',
                    'breed' => 'Plott Hound',
                    'age' => 3
                ]
            ]
        ];
        $this->assertArraySubset($expectedJson, json_decode((string) $response->getBody(), true));
        $this->assertSame(200, $response->getStatusCode());
    }

    public function testShowDogFound() {
        $dogId = Uuid::uuid4();
        $mockDogRepository = $this->getMockBuilder(DogRepository::class)->disableOriginalConstructor()->getMock();
        $mockDogRepository->expects($this->once())
                     ->method('find')
                     ->with(
                         $this->callback(function($param) use($dogId) {
                            return (string) $param === (string) $dogId;
                         })
                     )
                     ->willReturn($this->createDog('Ginapher', 'Boxer', 6));

        $request = (new ServerRequest())->withAttribute('id', (string) $dogId);

        $subject = new DogController($mockDogRepository, new \League\Fractal\Manager());

        $response = $subject->show($request);

        $expectedJson = [
            'data' => [
                'name' => 'Ginapher',
                'breed' => 'Boxer',
                'age' => 6
            ]
        ];
        $this->assertArraySubset($expectedJson, json_decode((string) $response->getBody(), true));
        $this->assertSame(200, $response->getStatusCode());
    }

    public function testShowDogNotFound() {
        $dogId = Uuid::uuid4();
        $mockDogRepository = $this->getMockBuilder(DogRepository::class)->disableOriginalConstructor()->getMock();
        $mockDogRepository->expects($this->once())
                     ->method('find')
                     ->willThrowException(new NotFoundException("Could not find a dog matching id 9876."));

        $request = (new ServerRequest())->withAttribute('id', $dogId);
        $subject = new DogController($mockDogRepository, new \League\Fractal\Manager());

        // emulating functionality we would expect to see in the controller dispatcher
        try {
            $subject->show($request);
        } catch (\Throwable $error) {
            $response = $subject->rescueFrom($error);
        }


        $expectedJson = ['message' => 'Not Found'];
        $this->assertArraySubset($expectedJson, json_decode((string) $response->getBody(), true));
        $this->assertSame(404, $response->getStatusCode());
    }

    public function testCreateValidDog() {
        $mockDogRepository = $this->getMockBuilder(DogRepository::class)->disableOriginalConstructor()->getMock();
        $mockDogRepository->expects($this->once())
                     ->method('save')
                     ->with(
                         $this->callback(function($param) {
                             return $param instanceof Dog && (
                                 $param->getName() === 'Missy' &&
                                 $param->getBreed() === 'Chihuahua' &&
                                 $param->getAge() === 13
                                 );
                         })
                     )->willReturn(true);

        $request = (new ServerRequest())->withParsedBody(['dog' => ['name' => 'Missy', 'breed' => 'Chihuahua', 'age' => 13]]);
        $subject = new DogController($mockDogRepository, new \League\Fractal\Manager());

        $response = $subject->create($request);

        $this->assertSame(201, $response->getStatusCode());
        $expected = [
            'data' => [
                'name' => 'Missy',
                'breed' => 'Chihuahua',
                'age' => 13
            ]
        ];
        $this->assertArraySubset($expected, json_decode((string) $response->getBody(), true));
    }

    public function testCreateInvalidDog() {
        $mockDogRepository = $this->getMockBuilder(DogRepository::class)->disableOriginalConstructor()->getMock();
        $mockDogRepository->expects($this->never())
            ->method('save');

        $request = (new ServerRequest())->withParsedBody(['dog' => ['name' => '', 'breed' => '', 'age' => 0]]);
        $subject = new DogController($mockDogRepository, new \League\Fractal\Manager());

        $response = $subject->create($request);

        $this->assertSame(422, $response->getStatusCode());
        $expected = [
            'data' => [
                'name' => ['name must have a length between 3 and 50'],
                'breed' => ['breed must have a length between 3 and 50'],
                'age' => ['age must be greater than 0']
            ]
        ];
        $this->assertSame($expected, json_decode((string) $response->getBody(), true));
    }

    public function testUpdateFoundDogWithName() {
        $ginapher = $this->createDog('Ginapher', 'Boxer', 6);
        $dogId = $ginapher->getId();
        $mockDogRepository = $this->getMockBuilder(DogRepository::class)->disableOriginalConstructor()->getMock();
        $mockDogRepository->expects($this->once())
            ->method('find')
            ->with(
                $this->callback(function($param) use($dogId) {
                    return (string) $param === (string) $dogId;
                })
            )
            ->willReturn($ginapher);
        $mockDogRepository->expects($this->once())
            ->method('save')
            ->with(
                $this->callback(function($param) use ($dogId) {
                    return $param instanceof Dog && (
                            $param->getName() === 'Crystalbelle' &&
                            $param->getBreed() === 'Boxer' &&
                            $param->getAge() === 6
                        );
                })
            )
            ->willReturn(true);

        $request = (new ServerRequest())->withAttribute('id', (string) $dogId)->withParsedBody([
            'dog' => ['id' => (string) $dogId, 'name' => 'Crystalbelle']
        ]);
        $subject = new DogController($mockDogRepository, new \League\Fractal\Manager());

        $response = $subject->update($request);

        $this->assertSame(200, $response->getStatusCode());
        $expected = [
            'data' => [
                'id' => (string) $dogId,
                'name' => 'Crystalbelle',
                'breed' => 'Boxer',
                'age' => 6
            ]
        ];
        $this->assertSame($expected, json_decode((string) $response->getBody(), true));
    }

    public function testUpdateFoundDogWithAged() {
        $ginapher = $this->createDog('Ginapher', 'Boxer', 6);
        $dogId = $ginapher->getId();
        $mockDogRepository = $this->getMockBuilder(DogRepository::class)->disableOriginalConstructor()->getMock();
        $mockDogRepository->expects($this->once())
            ->method('find')
            ->with(
                $this->callback(function($param) use($dogId) {
                    return (string) $param === (string) $dogId;
                })
            )
            ->willReturn($ginapher);
        $mockDogRepository->expects($this->once())
            ->method('save')
            ->with(
                $this->callback(function($param) use ($dogId) {
                    return $param instanceof Dog && (
                            $param->getName() === 'Ginapher' &&
                            $param->getBreed() === 'Boxer' &&
                            $param->getAge() === 7
                        );
                })
            )
            ->willReturn(true);

        $request = (new ServerRequest())->withAttribute('id', (string) $dogId)->withParsedBody([
            'dog' => ['id' => (string) $dogId, 'aged' => true]
        ]);
        $subject = new DogController($mockDogRepository, new \League\Fractal\Manager());

        $response = $subject->update($request);

        $this->assertSame(200, $response->getStatusCode());
        $expected = [
            'data' => [
                'id' => (string) $dogId,
                'name' => 'Ginapher',
                'breed' => 'Boxer',
                'age' => 7
            ]
        ];
        $this->assertSame($expected, json_decode((string) $response->getBody(), true));
    }

    public function testUpdateFoundDogWithInvalidData() {
        $ginapher = $this->createDog('Ginapher', 'Boxer', 6);
        $dogId = $ginapher->getId();
        $mockDogRepository = $this->getMockBuilder(DogRepository::class)->disableOriginalConstructor()->getMock();
        $mockDogRepository->expects($this->once())
            ->method('find')
            ->with(
                $this->callback(function($param) use($dogId) {
                    return (string) $param === (string) $dogId;
                })
            )
            ->willReturn($ginapher);
        $mockDogRepository->expects($this->never())
            ->method('save');

        $request = (new ServerRequest())->withAttribute('id', (string) $dogId)->withParsedBody([
            'dog' => ['id' => (string) $dogId, 'name' => '&*^&*^&*^']
        ]);
        $subject = new DogController($mockDogRepository, new \League\Fractal\Manager());

        $response = $subject->update($request);

        $this->assertSame(422, $response->getStatusCode());
        $expected = [
            'data' => [
                'name' => ['name may only contain letters and spaces']
            ]
        ];
        $this->assertSame($expected, json_decode((string) $response->getBody(), true));
    }

    public function testDeleteDogFound() {
        $dogId = Uuid::uuid4();
        $mockDogRepository = $this->getMockBuilder(DogRepository::class)->disableOriginalConstructor()->getMock();
        $mockDogRepository->expects($this->once())
            ->method('delete')
            ->with(
                $this->callback(function($param) use($dogId) {
                    return (string) $param === (string) $dogId;
                })
            );

        $request = (new ServerRequest())->withAttribute('id', $dogId);
        $subject = new DogController($mockDogRepository, new \League\Fractal\Manager());

        $response = $subject->delete($request);

        $this->assertEmpty((string) $response->getBody());
        $this->assertSame(204, $response->getStatusCode());
    }

}