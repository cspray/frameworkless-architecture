<?php declare(strict_types=1);

namespace Cspray\ArchDemo\Test\Controller;

use Cspray\ArchDemo\Controller\MethodNotAllowedController;
use PHPUnit\Framework\TestCase;

class MethodNotAllowedsControllerTest extends TestCase
{

    public function testIndex()
    {
        $subject = new MethodNotAllowedController();

        $response = $subject->index();

        $expected = [
            'message' => 'Method Not Allowed'
        ];
        $this->assertArraySubset($expected, json_decode((string) $response->getBody(), true));
        $this->assertSame(405, $response->getStatusCode());
    }
}
