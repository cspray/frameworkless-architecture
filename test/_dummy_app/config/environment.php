<?php declare(strict_types=1);

return function (string $environment) {
    $config = [];

    switch ($environment) {
        case 'development':
            $config['db'] = [
                'host' => 'dev-host',
                'name' => 'dev-name',
                'user' => 'dev-user',
                'pass' => 'dev-pass',
                'driver' => 'dev-driver'
            ];
            $config['cors'] = [
                'serverOrigin' => 'http://example.com:1234',
                'preflightCacheMaxAge' => 12345,
                'forceMethodsPreflight' => true,
                'forceHeadersPreflight' => true,
                'forceCheckHost' => true,
                'requestCredentialsSupported' => true,
                'allowedOrigins' => [
                    'a' => true,
                    'b' => false,
                    'c' => null
                ],
                'allowedMethods' => [
                    'GET' => true,
                    'POST' => false,
                    'DELETE' => null
                ],
                'allowedHeaders' => [
                    'a' => true,
                    'b' => false,
                    'c' => null
                ],
                'responseExposedHeaders' => [
                    'z' => true,
                    'y' => false,
                    'x' => null
                ]
            ];
            break;
        case 'test':
            $config['db'] = [
                'host' => 'test-host',
                'name' => 'test-name',
                'user' => 'test-user',
                'pass' => 'test-pass',
                'driver' => 'test-driver'
            ];
            break;
    }

    return $config;
};
