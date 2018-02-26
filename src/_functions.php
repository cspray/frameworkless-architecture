<?php declare(strict_types=1);

namespace Cspray\ArchDemo;

use Auryn\Injector;
use Cspray\ArchDemo\Config\Environment;
use Doctrine\DBAL\Types\Type as DoctrineType;

function bootstrap(string $environment) : Injector
{
    static $injectors = [];

    if (!isset($injectors[$environment])) {
        $configPath = dirname(__DIR__) . '/config/environment.php';
        $envConfig = Environment::loadFromPhpFile($environment, $configPath);
        $injectors[$environment] = (new ObjectGraph($envConfig))->createContainer();
    }

    if (!DoctrineType::hasType('uuid')) {
        DoctrineType::addType('uuid', 'Ramsey\Uuid\Doctrine\UuidType');
    }

    if (!DoctrineType::hasType('uuid_binary')) {
        DoctrineType::addType('uuid_binary', 'Ramsey\Uuid\Doctrine\UuidBinaryType');
    }

    return $injectors[$environment];
}
