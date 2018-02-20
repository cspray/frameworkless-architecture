<?php declare(strict_types=1);


namespace Cspray\ArchDemo\Test\Middleware;

use Cspray\ArchDemo\Config\CorsConfig;
use Cspray\ArchDemo\Middleware\CorsMiddleware;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Zend\Diactoros\Response;
use Zend\Diactoros\ServerRequest;
use Zend\Diactoros\Stream;

class CorsMiddlewareTest extends TestCase {

    public function testReturnsErrorCodeWhenOriginsDoNotMatch() {
        $request = (new ServerRequest())->withHeader('Origin', 'http://example.com:80');
        $config = new CorsConfig(['serverOrigin' => 'https://not.example.com:80']);
        $subject = new CorsMiddleware($config);


        $response = $subject->process($request, $this->requestHandler());

        $this->assertSame(403, $response->getStatusCode());
    }

    public function testReturnsResponseFromRequestHandler() {
        $request = (new ServerRequest())->withHeader('Origin', 'http://example.com:80')->withMethod('GET');
        $config = new CorsConfig([
            'serverOrigin' => 'http://example.com:80',
            'allowedOrigins' => [
                'https://example.com:80' => true
            ],
            'allowedMethods' => [
                'GET' => true
            ]
        ]);
        $subject = new CorsMiddleware($config);


        $response = $subject->process($request, $this->requestHandler());

        $this->assertSame(200, $response->getStatusCode());
    }

    private function requestHandler() : RequestHandlerInterface {
        return new class implements RequestHandlerInterface {

            /**
             * Handle the request and return a response.
             */
            public function handle(ServerRequestInterface $request): ResponseInterface {
                $body = new Stream('php://temp', 'wb');
                $body->write('Ok');
                return (new Response())->withStatus(200)->withBody($body);
            }

        };
    }

}