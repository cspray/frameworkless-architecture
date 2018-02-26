<?php declare(strict_types=1);

namespace Cspray\ArchDemo\Middleware;

use Cspray\ArchDemo\Config\CorsConfig;
use Middlewares\Cors;
use Neomerx\Cors\Analyzer;
use Neomerx\Cors\Strategies\Settings;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class CorsMiddleware implements MiddlewareInterface
{

    private $config;

    public function __construct(CorsConfig $corsConfig)
    {
        $this->config = $corsConfig;
    }

    /**
     * Process an incoming server request and return a response, optionally delegating
     * response creation to a handler.
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $settings = new Settings();
        $settings->setServerOrigin($this->config->serverOrigin());
        $settings->setRequestAllowedOrigins($this->config->allowedOrigins());
        $settings->setRequestAllowedHeaders($this->config->allowedHeaders());
        $settings->setRequestAllowedMethods($this->config->allowedMethods());
        $settings->setCheckHost($this->config->forceCheckHost());
        $forcedPreflightHeaders = $this->config->forceAddAllowedHeadersToPreflightResponse();
        $settings->setForceAddAllowedHeadersToPreFlightResponse($forcedPreflightHeaders);
        $forcePreflightMethods = $this->config->forceAddAllowedMethodsToPreflightResponse();
        $settings->setForceAddAllowedMethodsToPreFlightResponse($forcePreflightMethods);

        $analyzer = Analyzer::instance($settings);

        return (new Cors($analyzer))->process($request, $handler);
    }
}
