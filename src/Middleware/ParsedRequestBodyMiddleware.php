<?php declare(strict_types=1);

namespace Cspray\ArchDemo\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class ParsedRequestBodyMiddleware implements MiddlewareInterface {

    private $contentTypes;

    /**
     * The contentTypeCallableMap should have a key that is the exact content type you expect in the header and the value
     * of that key should be a callable function that accepts a string value and returns the parsed body for that content
     * type.
     *
     * @param array $contentTypeCallableMap
     */
    public function __construct(array $contentTypeCallableMap = []) {
        $defaults = [
            'application/json' => [$this, 'parseJson']
        ];

        $this->contentTypes = array_merge($defaults, $contentTypeCallableMap);
    }

    /**
     * Process an incoming server request and return a response, optionally delegating
     * response creation to a handler.
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface {
        $contentType = $request->getHeader('Content-Type')[0] ?? null;
        if (isset($this->contentTypes[$contentType]) && is_callable($this->contentTypes[$contentType])) {
            $request = $request->withParsedBody($this->contentTypes[$contentType]((string) $request->getBody()));
        }

        return $handler->handle($request);
    }

    private function parseJson(string $input) {
        return json_decode($input, true);
    }


}