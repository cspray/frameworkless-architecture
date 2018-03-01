<?php declare(strict_types=1);

return function(string $environment = 'development') {
    $config = [];

    switch ($environment) {
        case 'development':
            $config['db'] = [
                'host' => 'postgres',
                'name' => 'archdemo',
                'user' => 'postgres',
                'pass' => '',
                'driver' => 'pdo_pgsql'
            ];

            $config['cors'] = [
                'serverOrigin' => 'http://localhost:3000',
                'allowedOrigins' => [
                    'http://127.0.0.1:4200' => true
                ],
                'allowedMethods' => [
                    'GET' => true,
                    'PUT' => true,
                    'POST' => true,
                    'DELETE' => true
                ]
            ];
            break;
        case 'test':
            // handle CI database hose for test environment
            $testHost = getenv('DB_HOST');
            if (!$testHost) {
                $testHost = 'postgres';
            }
            $config['db'] = [
                'host' => $testHost,
                'name' => 'archdemo_test',
                'user' => 'postgres',
                'pass' => '',
                'driver' => 'pdo_pgsql'
            ];
            break;
        case 'production':
            $config['db'] = [
                'host' => getenv('DB_HOST'),
                'name' => getenv('DB_NAME'),
                'user' => getenv('DB_USER'),
                'pass' => getenv('DB_PASS'),
                'driver' => getenv('DB_DRIVER')
            ];

            break;
    }

    return $config;
};