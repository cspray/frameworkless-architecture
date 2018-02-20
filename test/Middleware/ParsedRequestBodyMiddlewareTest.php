<?php declare(strict_types=1);

namespace Cspray\ArchDemo\Test\Middleware;

use Cspray\ArchDemo\Middleware\ParsedRequestBodyMiddleware;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Zend\Diactoros\Response;
use Zend\Diactoros\ServerRequest;
use Zend\Diactoros\Stream;

class ParsedRequestBodyMiddlewareTest extends TestCase {

    public function testParsingJsonWithContentType() {
        $expected = ['a' => 1, 'b' => true, 'c' => 'foo'];
        $jsonString = json_encode($expected);
        $body = new Stream('php://temp', 'wb');
        $body->write($jsonString);
        $request = (new ServerRequest())->withHeader('Content-Type', 'application/json')->withBody($body);
        $requestHandler = $this->requestHandler();

        $subject = new ParsedRequestBodyMiddleware();
        $subject->process($request, $requestHandler);
        $parsedRequest = $requestHandler->getRequest();
        $this->assertSame($expected, $parsedRequest->getParsedBody());
    }

    public function testParsingJsonDoesNotHappenWithNoContentType() {
        $expected = ['a' => 1, 'b' => true, 'c' => 'foo'];
        $jsonString = json_encode($expected);
        $body = new Stream('php://temp', 'wb');
        $body->write($jsonString);
        $request = (new ServerRequest())->withHeader('Content-Type', 'text/plain')->withBody($body);
        $requestHandler = $this->requestHandler();

        $subject = new ParsedRequestBodyMiddleware();
        $subject->process($request, $requestHandler);
        $parsedRequest = $requestHandler->getRequest();
        $this->assertNull($parsedRequest->getParsedBody());
    }

    public function testParsingNoContentTypeSet() {
        $expected = ['a' => 1, 'b' => true, 'c' => 'foo'];
        $jsonString = json_encode($expected);
        $body = new Stream('php://temp', 'wb');
        $body->write($jsonString);
        $request = (new ServerRequest())->withBody($body);
        $requestHandler = $this->requestHandler();

        $subject = new ParsedRequestBodyMiddleware();
        $subject->process($request, $requestHandler);
        $parsedRequest = $requestHandler->getRequest();
        $this->assertNull($parsedRequest->getParsedBody());
    }

    private function requestHandler() : RequestHandlerInterface {
        return new class implements RequestHandlerInterface {

            private $request;

            /**
             * Handle the request and return a response.
             */
            public function handle(ServerRequestInterface $request): ResponseInterface {
                $this->request = $request;
                return new Response();
            }

            public function getRequest() : ServerRequestInterface {
                return $this->request;
            }
        };
    }



}