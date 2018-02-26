<?php declare(strict_types=1);

namespace Cspray\ArchDemo\Config;

class Environment
{

    private $environment;
    private $config;
    private $dbConfig;
    private $corsConfig;

    private function __construct(string $environment, array $data)
    {
        $this->environment = $environment;
        $this->config = $data;
    }

    public static function loadFromPhpFile(string $environment, string $phpFile) : Environment
    {
        $configFromFile = require($phpFile);
        $config = $configFromFile($environment);
        return new self($environment, $config);
    }

    public static function loadFromArray(string $environment, array $actualConfig) : Environment
    {
        return new self($environment, $actualConfig);
    }

    public function environmentName() : string
    {
        return $this->environment;
    }

    public function databaseConfig() : DatabaseConfig
    {
        if (!$this->dbConfig) {
            $this->dbConfig = new DatabaseConfig($this->config['db'] ?? []);
        }

        return $this->dbConfig;
    }

    public function corsConfig() : CorsConfig
    {
        if (!$this->corsConfig) {
            $this->corsConfig = new CorsConfig($this->config['cors'] ?? []);
        }

        return $this->corsConfig;
    }
}
