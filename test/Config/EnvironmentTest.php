<?php declare(strict_types=1);

namespace Cspray\ArchDemo\Test\Config;

use Cspray\ArchDemo\Config\Environment;
use PHPUnit\Framework\TestCase;

class EnvironmentTest extends TestCase {

    private $dummyConfigPath;

    public function setUp() {
        parent::setUp();
        $this->dummyConfigPath = dirname(__DIR__) . '/_dummy_app/config/environment.php';
    }

    public function environmentFileConfigProvider() {
        return [
            ['development', 'environmentName', 'development'],
            ['development', 'databaseDriver', 'dev-driver'],
            ['development', 'databaseHost', 'dev-host'],
            ['development', 'databaseName', 'dev-name'],
            ['development', 'databaseUser', 'dev-user'],
            ['development', 'databasePassword', 'dev-pass'],
            ['test', 'environmentName', 'test'],
            ['test', 'databaseDriver', 'test-driver'],
            ['test', 'databaseHost', 'test-host'],
            ['test', 'databaseName', 'test-name'],
            ['test', 'databaseUser', 'test-user'],
            ['test', 'databasePassword', 'test-pass']
        ];
    }

    /**
     * @dataProvider environmentFileConfigProvider
     */
    public function testLoadFromFileRespectsEnvironment(string $environment, string $method, string $expected) {
        $config = Environment::loadFromPhpFile($environment, $this->dummyConfigPath);

        $this->assertSame($expected, $config->$method());
    }

    public function environmentArrayConfigProvider() {
        return [
            ['development', [], 'environmentName', 'development'],
            ['development', ['db.driver' => 'array-driver'], 'databaseDriver', 'array-driver'],
            ['development', ['db.host' => 'array-host'], 'databaseHost', 'array-host'],
            ['development', ['db.name' => 'array-name'], 'databaseName', 'array-name'],
            ['development', ['db.user' => 'array-user'], 'databaseUser', 'array-user'],
            ['development', ['db.pass' => 'array-pass'], 'databasePassword', 'array-pass'],
        ];
    }

    /**
     * @dataProvider environmentArrayConfigProvider
     */
    public function testLoadFromArray(string $environment, array $actualData, string $method, string $expected) {
        $config = Environment::loadFromArray($environment, $actualData);

        $this->assertSame($expected, $config->$method());
    }

}