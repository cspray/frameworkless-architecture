<?php declare(strict_types=1);

namespace Cspray\ArchDemo\Test\Controller;

use Cspray\ArchDemo\Controller\NotFoundController;
use PHPUnit\Framework\TestCase;

class NotFoundControllerTest extends TestCase
{

    public function testIndex()
    {
        $subject = new NotFoundController();

        $response = $subject->index();

        $expected = [
            'message' => 'Not Found'
        ];
        $this->assertArraySubset($expected, json_decode((string) $response->getBody(), true));
        $this->assertSame(404, $response->getStatusCode());
    }
}
