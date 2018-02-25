<?php declare(strict_types=1);


namespace Cspray\ArchDemo\Test\Controller;

use Cspray\ArchDemo\Controller\TrainerController;
use Cspray\ArchDemo\Entity\Trainer;
use Cspray\ArchDemo\Repository\TrainerRepository;
use Cspray\ArchDemo\Exception\NotFoundException;
use Ramsey\Uuid\Uuid;
use Zend\Diactoros\ServerRequest;
use PHPUnit\Framework\TestCase;
use ArrayObject;

class TrainerControllerTest extends TestCase {

    private function createTrainer(string $name, string $specialty) : Trainer {
        return new Trainer($name, $specialty);
    }

    public function testIndex() {
        $trainers = [
            $this->createTrainer('Br Christopher', 'Obedience'),
            $this->createTrainer('Charles', 'Agility'),
            $this->createTrainer('Sarah', 'Herding')
        ];
        $mockRepository = $this->getMockBuilder(TrainerRepository::class)->disableOriginalConstructor()->getMock();
        $mockRepository->expects($this->once())->method('findAll')->willReturn(new ArrayObject($trainers));
        $subject = new TrainerController($mockRepository, new \League\Fractal\Manager());

        $response = $subject->index(new ServerRequest());

        $expectedJson = [
            'data' => [
                [
                    'name' => 'Br Christopher',
                    'specialty' => 'Obedience'
                ],
                [
                    'name' => 'Charles',
                    'specialty' => 'Agility'
                ],
                [
                    'name' => 'Sarah',
                    'specialty' => 'Herding'
                ]
            ]
        ];
        $this->assertArraySubset($expectedJson, json_decode((string) $response->getBody(), true));
        $this->assertSame(200, $response->getStatusCode());
    }

    public function testShowTrainerFound() {
        $trainerId = Uuid::uuid4();
        $mockRepository = $this->getMockBuilder(TrainerRepository::class)->disableOriginalConstructor()->getMock();
        $mockRepository->expects($this->once())
            ->method('find')
            ->with(
                $this->callback(function($param) use($trainerId) {
                    return (string) $param === (string) $trainerId;
                })
            )
            ->willReturn($this->createTrainer('Aaron', 'Spoiling'));

        $request = (new ServerRequest())->withAttribute('id', (string) $trainerId);

        $subject = new TrainerController($mockRepository, new \League\Fractal\Manager());

        $response = $subject->show($request);

        $expectedJson = [
            'data' => [
                'name' => 'Aaron',
                'specialty' => 'Spoiling'
            ]
        ];
        $this->assertArraySubset($expectedJson, json_decode((string) $response->getBody(), true));
        $this->assertSame(200, $response->getStatusCode());
    }

    public function testShowTrainerNotFound() {
        $trainerId = Uuid::uuid4();
        $mockRepository = $this->getMockBuilder(TrainerRepository::class)->disableOriginalConstructor()->getMock();
        $mockRepository->expects($this->once())
            ->method('find')
            ->willThrowException(new NotFoundException("Could not find a trainer matching id 9876."));

        $request = (new ServerRequest())->withAttribute('id', $trainerId);
        $subject = new TrainerController($mockRepository, new \League\Fractal\Manager());

        // emulating functionality we would expect to see in the controller dispatcher
        try {
            $response = $subject->show($request);
        } catch (\Throwable $error) {
            $response = $subject->rescueFrom($error);
        }


        $expectedJson = ['message' => 'Not Found'];
        $this->assertArraySubset($expectedJson, json_decode((string) $response->getBody(), true));
        $this->assertSame(404, $response->getStatusCode());
    }

    public function testCreateValidTrainer() {
        $mockRepository = $this->getMockBuilder(TrainerRepository::class)->disableOriginalConstructor()->getMock();
        $mockRepository->expects($this->once())
            ->method('save')
            ->with(
                $this->callback(function($param) {
                    return $param instanceof Trainer && (
                            $param->getName() === 'Charles' &&
                            $param->getSpecialty() === 'Obedience'
                        );
                })
            )->willReturn(true);

        $request = (new ServerRequest())->withParsedBody(['trainer' => ['name' => 'Charles', 'specialty' => 'Obedience']]);
        $subject = new TrainerController($mockRepository, new \League\Fractal\Manager());

        $response = $subject->create($request);

        $this->assertSame(201, $response->getStatusCode());
        $expected = [
            'data' => [
                'name' => 'Charles',
                'specialty' => 'Obedience'
            ]
        ];
        $this->assertArraySubset($expected, json_decode((string) $response->getBody(), true));
    }

    public function testCreateInvalidTrainer() {
        $mockRepository = $this->getMockBuilder(TrainerRepository::class)->disableOriginalConstructor()->getMock();
        $mockRepository->expects($this->never())
            ->method('save');

        $request = (new ServerRequest())->withParsedBody(['trainer' => ['name' => '', 'specialty' => '']]);
        $subject = new TrainerController($mockRepository, new \League\Fractal\Manager());

        $response = $subject->create($request);

        $this->assertSame(422, $response->getStatusCode());
        $expected = [
            'data' => [
                'name' => ['name must have a length between 3 and 50'],
                'specialty' => ['specialty must have a length between 5 and 500']
            ]
        ];
        $this->assertSame($expected, json_decode((string) $response->getBody(), true));
    }

    public function testUpdateFoundTrainerWithName() {
        $ginapher = $this->createTrainer('Ginapher', 'Agility Training');
        $trainerId = $ginapher->getId();
        $mockRepository = $this->getMockBuilder(TrainerRepository::class)->disableOriginalConstructor()->getMock();
        $mockRepository->expects($this->once())
            ->method('find')
            ->with(
                $this->callback(function($param) use($trainerId) {
                    return (string) $param === (string) $trainerId;
                })
            )
            ->willReturn($ginapher);
        $mockRepository->expects($this->once())
            ->method('save')
            ->with(
                $this->callback(function($param) use ($trainerId) {
                    return $param instanceof Trainer && (
                            $param->getName() === 'Crystalbelle' &&
                            $param->getSpecialty() === 'Agility Training'
                        );
                })
            )
            ->willReturn(true);

        $request = (new ServerRequest())->withAttribute('id', (string) $trainerId)->withParsedBody([
            'trainer' => ['id' => (string) $trainerId, 'name' => 'Crystalbelle']
        ]);
        $subject = new TrainerController($mockRepository, new \League\Fractal\Manager());

        $response = $subject->update($request);

        $this->assertSame(200, $response->getStatusCode());
        $expected = [
            'data' => [
                'id' => (string) $trainerId,
                'name' => 'Crystalbelle',
                'specialty' => 'Agility Training'
            ]
        ];
        $this->assertSame($expected, json_decode((string) $response->getBody(), true));
    }

    public function testUpdateFoundTrainerWithSpecialty() {
        $trainer = $this->createTrainer('Ted', 'Tracking');
        $trainerId = $trainer->getId();
        $mockRepository = $this->getMockBuilder(TrainerRepository::class)->disableOriginalConstructor()->getMock();
        $mockRepository->expects($this->once())
            ->method('find')
            ->with(
                $this->callback(function($param) use($trainerId) {
                    return (string) $param === (string) $trainerId;
                })
            )
            ->willReturn($trainer);
        $mockRepository->expects($this->once())
            ->method('save')
            ->with(
                $this->callback(function($param) use ($trainerId) {
                    return $param instanceof Trainer && (
                            $param->getName() === 'Ted' &&
                            $param->getSpecialty() === 'Agility'
                        );
                })
            )
            ->willReturn(true);

        $request = (new ServerRequest())->withAttribute('id', (string) $trainerId)->withParsedBody([
            'trainer' => ['id' => (string) $trainerId, 'specialty' => 'Agility']
        ]);
        $subject = new TrainerController($mockRepository, new \League\Fractal\Manager());

        $response = $subject->update($request);

        $this->assertSame(200, $response->getStatusCode());
        $expected = [
            'data' => [
                'id' => (string) $trainerId,
                'name' => 'Ted',
                'specialty' => 'Agility'
            ]
        ];
        $this->assertSame($expected, json_decode((string) $response->getBody(), true));
    }

    public function testUpdateFoundTrainerWithInvalidData() {
        $ginapher = $this->createTrainer('Ginapher', 'Obedience');
        $trainerId = $ginapher->getId();
        $mockRepository = $this->getMockBuilder(TrainerRepository::class)->disableOriginalConstructor()->getMock();
        $mockRepository->expects($this->once())
            ->method('find')
            ->with(
                $this->callback(function($param) use($trainerId) {
                    return (string) $param === (string) $trainerId;
                })
            )
            ->willReturn($ginapher);
        $mockRepository->expects($this->never())
            ->method('save');

        $request = (new ServerRequest())->withAttribute('id', (string) $trainerId)->withParsedBody([
            'trainer' => ['id' => (string) $trainerId, 'name' => '&*^&*^&*^']
        ]);
        $subject = new TrainerController($mockRepository, new \League\Fractal\Manager());

        $response = $subject->update($request);

        $this->assertSame(422, $response->getStatusCode());
        $expected = [
            'data' => [
                'name' => ['name may only contain letters and spaces']
            ]
        ];
        $this->assertSame($expected, json_decode((string) $response->getBody(), true));
    }

    public function testDeleteTrainerFound() {
        $trainerId = Uuid::uuid4();
        $mockRepository = $this->getMockBuilder(TrainerRepository::class)->disableOriginalConstructor()->getMock();
        $mockRepository->expects($this->once())
            ->method('delete')
            ->with(
                $this->callback(function($param) use($trainerId) {
                    return (string) $param === (string) $trainerId;
                })
            );

        $request = (new ServerRequest())->withAttribute('id', $trainerId);
        $subject = new TrainerController($mockRepository, new \League\Fractal\Manager());

        $response = $subject->delete($request);

        $this->assertEmpty((string) $response->getBody());
        $this->assertSame(204, $response->getStatusCode());
    }
}