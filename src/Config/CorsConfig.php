<?php declare(strict_types=1);

namespace Cspray\ArchDemo\Config;

use Middlewares\Utils\Factory\UriFactory;
use Psr\Http\Message\UriInterface;
use Zend\Diactoros\Uri;

class CorsConfig {

    private $config;

    public function __construct(array $data = []) {
        $this->config = $data;
    }

    public function serverOrigin() : UriInterface {
        return (new UriFactory())->createUri($this->config['serverOrigin'] ?? '');
    }

    public function preflightCacheMaxAge() : int {
        return $this->config['preflightCacheMaxAge'] ?? 0;
    }

    public function forceAddAllowedMethodsToPreflightResponse() : bool {
        return $this->config['forceMethodsPreflight'] ?? false;
    }

    public function forceAddAllowedHeadersToPreflightResponse() : bool {
        return $this->config['forceHeadersPreflight'] ?? false;
    }

    public function forceCheckHost() : bool {
        return $this->config['forceCheckHost'] ?? false;
    }

    public function requestCredentialsSupported() : bool {
        return $this->config['requestCredentialsSupported'] ?? false;
    }

    public function allowedOrigins() : array {
        return $this->config['allowedOrigins'] ?? [];
    }

    public function allowedMethods() : array {
        return $this->config['allowedMethods'] ?? [];
    }

    public function allowedHeaders() : array {
        return $this->config['allowedHeaders'] ?? [];
    }

    public function responseExposedHeaders() : array {
        return $this->config['responseExposedHeaders'] ?? [];
    }

}