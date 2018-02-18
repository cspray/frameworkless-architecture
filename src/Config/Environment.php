<?php declare(strict_types=1);

namespace Cspray\ArchDemo\Config;

class Environment {

    private $environment;
    private $config;

    private function __construct(string $environment, array $data) {
        $this->environment = $environment;
        $this->config = $data;
    }

    public static function loadFromPhpFile(string $environment, string $phpFile) : Environment {
        $configFromFile = require($phpFile);
        $config = $configFromFile($environment);
        return new self($environment, $config);
    }

    public static function loadFromArray(string $environment, array $actualConfig) : Environment {
        return new self($environment, $actualConfig);
    }

    public function environmentName() : string {
        return $this->environment ?? '';
    }

    public function databaseDriver() : string {
        return $this->config['db.driver'] ?? '';
    }

    public function databaseName() : string {
        return $this->config['db.name'] ?? '';
    }

    public function databaseHost() : string {
        return $this->config['db.host'] ?? '';
    }

    public function databaseUser() : string {
        return $this->config['db.user'] ?? '';
    }

    public function databasePassword() : string {
        return $this->config['db.pass'] ?? '';
    }

}