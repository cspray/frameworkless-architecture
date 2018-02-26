<?php declare(strict_types=1);


namespace Cspray\ArchDemo\Config;

class DatabaseConfig
{

    private $config;

    public function __construct(array $data = [])
    {
        $this->config = $data;
    }

    public function driver() : string
    {
        return $this->config['driver'] ?? '';
    }

    public function name() : string
    {
        return $this->config['name'] ?? '';
    }

    public function host() : string
    {
        return $this->config['host'] ?? '';
    }

    public function user() : string
    {
        return $this->config['user'] ?? '';
    }

    public function password() : string
    {
        return $this->config['pass'] ?? '';
    }
}
