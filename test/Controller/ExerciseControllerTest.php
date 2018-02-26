<?php declare(strict_types=1);


namespace Cspray\ArchDemo\Test\Controller;

use Cspray\ArchDemo\Controller\ExerciseController;
use Cspray\ArchDemo\Entity\Exercise;
use Cspray\ArchDemo\Repository\ExerciseRepository;
use Cspray\ArchDemo\Exception\NotFoundException;
use Ramsey\Uuid\Uuid;
use Zend\Diactoros\ServerRequest;
use PHPUnit\Framework\TestCase;
use ArrayObject;

class ExerciseControllerTest extends TestCase
{

    private function createExercise(string $name, string $description) : Exercise
    {
        return new Exercise($name, $description);
    }

    public function testIndex()
    {
        $heelDescription = 'Have your dog walk politely at your side; never lagging behind or pulling on the lead.';
        $sitDescription = 'Have your dog sit and hold position for an incrementing amount of time.';
        $comeDescription = 'From a varying distance have your dog come to you.';
        $exercises = [
            $this->createExercise('Heel', $heelDescription),
            $this->createExercise('Sit', $sitDescription),
            $this->createExercise('Come', $comeDescription)
        ];
        $mockRepository = $this->getMockBuilder(ExerciseRepository::class)->disableOriginalConstructor()->getMock();
        $mockRepository->expects($this->once())->method('findAll')->willReturn(new ArrayObject($exercises));
        $subject = new ExerciseController($mockRepository, new \League\Fractal\Manager());

        $response = $subject->index(new ServerRequest());

        $expectedJson = [
            'data' => [
                [
                    'name' => 'Heel',
                    'description' => $heelDescription
                ],
                [
                    'name' => 'Sit',
                    'description' => $sitDescription
                ],
                [
                    'name' => 'Come',
                    'description' => $comeDescription
                ]
            ]
        ];
        $this->assertArraySubset($expectedJson, json_decode((string) $response->getBody(), true));
        $this->assertSame(200, $response->getStatusCode());
    }

    public function testShowExerciseFound()
    {
        $exerciseId = Uuid::uuid4();
        $description = 'Stimulate your dog\'s sense of smell and have it track down pungent treats';
        $mockRepository = $this->getMockBuilder(ExerciseRepository::class)->disableOriginalConstructor()->getMock();
        $mockRepository->expects($this->once())
            ->method('find')
            ->with(
                $this->callback(function ($param) use ($exerciseId) {
                    return (string) $param === (string) $exerciseId;
                })
            )
            ->willReturn($this->createExercise('Tracking', $description));

        $request = (new ServerRequest())->withAttribute('id', (string) $exerciseId);

        $subject = new ExerciseController($mockRepository, new \League\Fractal\Manager());

        $response = $subject->show($request);

        $expectedJson = [
            'data' => [
                'name' => 'Tracking',
                'description' => $description
            ]
        ];
        $this->assertArraySubset($expectedJson, json_decode((string) $response->getBody(), true));
        $this->assertSame(200, $response->getStatusCode());
    }

    public function testShowExerciseNotFound()
    {
        $exerciseId = Uuid::uuid4();
        $mockRepository = $this->getMockBuilder(ExerciseRepository::class)->disableOriginalConstructor()->getMock();
        $mockRepository->expects($this->once())
            ->method('find')
            ->willThrowException(new NotFoundException("Could not find a exercise matching id 9876."));

        $request = (new ServerRequest())->withAttribute('id', $exerciseId);
        $subject = new ExerciseController($mockRepository, new \League\Fractal\Manager());

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

    public function testCreateValidExercise()
    {
        $mockRepository = $this->getMockBuilder(ExerciseRepository::class)->disableOriginalConstructor()->getMock();
        $mockRepository->expects($this->once())
            ->method('save')
            ->with(
                $this->callback(function ($param) {
                    return $param instanceof Exercise && (
                            $param->getName() === 'Stay' &&
                            $param->getDescription() === 'Convince your pooch to hold its position'
                        );
                })
            )->willReturn(true);

        $parsedBody = ['exercise' => ['name' => 'Stay', 'description' => 'Convince your pooch to hold its position']];
        $request = (new ServerRequest())->withParsedBody($parsedBody);
        $subject = new ExerciseController($mockRepository, new \League\Fractal\Manager());

        $response = $subject->create($request);

        $this->assertSame(201, $response->getStatusCode());
        $expected = [
            'data' => [
                'name' => 'Stay',
                'description' => 'Convince your pooch to hold its position'
            ]
        ];
        $this->assertArraySubset($expected, json_decode((string) $response->getBody(), true));
    }

    public function testCreateInvalidExercise()
    {
        $mockRepository = $this->getMockBuilder(ExerciseRepository::class)->disableOriginalConstructor()->getMock();
        $mockRepository->expects($this->never())
            ->method('save');

        $request = (new ServerRequest())->withParsedBody(['exercise' => ['name' => '', 'description' => '']]);
        $subject = new ExerciseController($mockRepository, new \League\Fractal\Manager());

        $response = $subject->create($request);

        $this->assertSame(422, $response->getStatusCode());
        $expected = [
            'data' => [
                'name' => ['name must have a length between 3 and 50'],
                'description' => ['description must have a length between 10 and 500']
            ]
        ];
        $this->assertSame($expected, json_decode((string) $response->getBody(), true));
    }

    public function testUpdateFoundExerciseWithName()
    {
        $description = 'Improve your dog\'s speed and stimulate them intellectually with this obstacle course.';
        $ginapher = $this->createExercise('Agility', $description);
        $exerciseId = $ginapher->getId();
        $mockRepository = $this->getMockBuilder(ExerciseRepository::class)->disableOriginalConstructor()->getMock();
        $mockRepository->expects($this->once())
            ->method('find')
            ->with(
                $this->callback(function ($param) use ($exerciseId) {
                    return (string) $param === (string) $exerciseId;
                })
            )
            ->willReturn($ginapher);
        $mockRepository->expects($this->once())
            ->method('save')
            ->with(
                $this->logicalAnd(
                    $this->isInstanceOf(Exercise::class),
                    $this->callback(function ($param) {
                        return $param->getName() === 'Agility Obstacle';
                    }),
                    $this->callback(function ($param) use ($description) {
                        return $param->getDescription() === $description;
                    })
                )
            )
            ->willReturn(true);

        $request = (new ServerRequest())->withAttribute('id', (string) $exerciseId)->withParsedBody([
            'exercise' => ['name' => 'Agility Obstacle']
        ]);
        $subject = new ExerciseController($mockRepository, new \League\Fractal\Manager());

        $response = $subject->update($request);

        $this->assertSame(200, $response->getStatusCode());
        $expected = [
            'data' => [
                'id' => (string) $exerciseId,
                'name' => 'Agility Obstacle',
                'description' => $description
            ]
        ];
        $this->assertSame($expected, json_decode((string) $response->getBody(), true));
    }

    public function testUpdateFoundExerciseWithDescription()
    {
        $exercise = $this->createExercise('Sit', 'Convince the dog to put its butt on the ground and keep it there.');
        $exerciseId = $exercise->getId();
        $mockRepository = $this->getMockBuilder(ExerciseRepository::class)->disableOriginalConstructor()->getMock();
        $mockRepository->expects($this->once())
            ->method('find')
            ->with(
                $this->callback(function ($param) use ($exerciseId) {
                    return (string) $param === (string) $exerciseId;
                })
            )
            ->willReturn($exercise);
        $mockRepository->expects($this->once())
            ->method('save')
            ->with(
                $this->callback(function ($param) use ($exerciseId) {
                    return $param instanceof Exercise && (
                            $param->getName() === 'Sit' &&
                            $param->getDescription() === 'Seriously, just keep your dog\'s ass on the ground'
                        );
                })
            )
            ->willReturn(true);

        $request = (new ServerRequest())->withAttribute('id', (string) $exerciseId)->withParsedBody([
            'exercise' => ['description' => 'Seriously, just keep your dog\'s ass on the ground']
        ]);
        $subject = new ExerciseController($mockRepository, new \League\Fractal\Manager());

        $response = $subject->update($request);

        $this->assertSame(200, $response->getStatusCode());
        $expected = [
            'data' => [
                'id' => (string) $exerciseId,
                'name' => 'Sit',
                'description' => 'Seriously, just keep your dog\'s ass on the ground'
            ]
        ];
        $this->assertSame($expected, json_decode((string) $response->getBody(), true));
    }

    public function testUpdateFoundExerciseWithInvalidData()
    {
        $ginapher = $this->createExercise('Heel', 'Have your dog walk comfortably at your side');
        $exerciseId = $ginapher->getId();
        $mockRepository = $this->getMockBuilder(ExerciseRepository::class)->disableOriginalConstructor()->getMock();
        $mockRepository->expects($this->once())
            ->method('find')
            ->with(
                $this->callback(function ($param) use ($exerciseId) {
                    return (string) $param === (string) $exerciseId;
                })
            )
            ->willReturn($ginapher);
        $mockRepository->expects($this->never())
            ->method('save');

        $request = (new ServerRequest())->withAttribute('id', (string) $exerciseId)->withParsedBody([
            'exercise' => ['name' => '&*^&*^&*^']
        ]);
        $subject = new ExerciseController($mockRepository, new \League\Fractal\Manager());

        $response = $subject->update($request);

        $this->assertSame(422, $response->getStatusCode());
        $expected = [
            'data' => [
                'name' => ['name may only contain letters and spaces']
            ]
        ];
        $this->assertSame($expected, json_decode((string) $response->getBody(), true));
    }

    public function testDeleteExerciseFound()
    {
        $exerciseId = Uuid::uuid4();
        $mockRepository = $this->getMockBuilder(ExerciseRepository::class)->disableOriginalConstructor()->getMock();
        $mockRepository->expects($this->once())
            ->method('delete')
            ->with(
                $this->callback(function ($param) use ($exerciseId) {
                    return (string) $param === (string) $exerciseId;
                })
            );

        $request = (new ServerRequest())->withAttribute('id', $exerciseId);
        $subject = new ExerciseController($mockRepository, new \League\Fractal\Manager());

        $response = $subject->delete($request);

        $this->assertEmpty((string) $response->getBody());
        $this->assertSame(204, $response->getStatusCode());
    }
}
