<?php declare(strict_types=1);


namespace Cspray\ArchDemo\Test\Controller;

use Cspray\ArchDemo\Controller\ApplicationController;
use Cspray\ArchDemo\Repository\Repository;
use League\Fractal\Manager;
use PHPUnit\Framework\TestCase;

class ApplicationControllerTest extends TestCase
{

    public function testRescueFromNotNotFoundThrowsException()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('My message');
        $mockRepo = $this->getMockBuilder(Repository::class)->getMock();
        $mockFractalManager = $this->getMockBuilder(Manager::class)->getMock();
        $subject = $this->getMockForAbstractClass(ApplicationController::class, [$mockRepo, $mockFractalManager]);
        $subject->rescueFrom(new \RuntimeException('My message'));
    }
}
