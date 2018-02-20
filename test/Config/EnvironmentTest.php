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

    public function testEnvironmentName() {
        $environment = Environment::loadFromPhpFile('development', $this->dummyConfigPath);

        $this->assertSame('development', $environment->environmentName());
    }

    public function environmentFileDatabaseConfigProvider() {
        return [
            ['development', 'driver', 'dev-driver'],
            ['development', 'host', 'dev-host'],
            ['development', 'name', 'dev-name'],
            ['development', 'user', 'dev-user'],
            ['development', 'password', 'dev-pass'],
            ['test', 'driver', 'test-driver'],
            ['test', 'host', 'test-host'],
            ['test', 'name', 'test-name'],
            ['test', 'user', 'test-user'],
            ['test', 'password', 'test-pass']
        ];
    }

    /**
     * @dataProvider environmentFileDatabaseConfigProvider
     */
    public function testLoadFromFileDatabaseConfig(string $environment, string $method, string $expected) {
        $config = Environment::loadFromPhpFile($environment, $this->dummyConfigPath);
        $dbConfig = $config->databaseConfig();

        $this->assertSame($expected, $dbConfig->$method());
    }

    public function environmentFileCorsConfigProvider() {
        return [
            ['development', 'preflightCacheMaxAge', 12345],
            ['development', 'forceAddAllowedMethodsToPreflightResponse', true],
            ['development', 'forceAddAllowedHeadersToPreflightResponse', true],
            ['development', 'forceCheckHost', true],
            ['development', 'requestCredentialsSupported', true],
            ['development', 'allowedOrigins', ['a' => true, 'b' => false, 'c' => null]],
            ['development', 'allowedMethods', ['GET' => true, 'POST' => false, 'DELETE' => null]],
            ['development', 'allowedHeaders', ['a' => true, 'b' => false, 'c' => null]],
            ['development', 'responseExposedHeaders', ['z' => true, 'y' => false, 'x' => null]]
        ];
    }

    /**
     * @dataProvider environmentFileCorsConfigProvider
     */
    public function testLoadFromFileCorsConfig(string $environment, string $method, $expected) {
        $config = Environment::loadFromPhpFile($environment, $this->dummyConfigPath);
        $corsConfig = $config->corsConfig();

        $this->assertSame($expected, $corsConfig->$method());
    }

    public function testLoadFromFileCorsServerOrigin() {
        $config = Environment::loadFromPhpFile('development', $this->dummyConfigPath);
        $corsConfig = $config->corsConfig();

        $uri = $corsConfig->serverOrigin();

        $this->assertSame('http', $uri->getScheme());
        $this->assertSame('example.com', $uri->getHost());
        $this->assertSame(1234, $uri->getPort());
    }

    public function environmentArrayDatabaseConfigProvider() {
        return [
            ['development', ['db' => ['driver' => 'array-driver']], 'driver', 'array-driver'],
            ['development', ['db' => ['host' => 'array-host']], 'host', 'array-host'],
            ['development', ['db' => ['name' => 'array-name']], 'name', 'array-name'],
            ['development', ['db' => ['user' => 'array-user']], 'user', 'array-user'],
            ['development', ['db' => ['pass' => 'array-pass']], 'password', 'array-pass'],
        ];
    }

    /**
     * @dataProvider environmentArrayDatabaseConfigProvider
     */
    public function testLoadFromArray(string $environment, array $actualData, string $method, string $expected) {
        $config = Environment::loadFromArray($environment, $actualData);
        $dbConfig = $config->databaseConfig();
        $this->assertSame($expected, $dbConfig->$method());
    }

}