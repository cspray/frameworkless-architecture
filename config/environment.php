<?php declare(strict_types=1);

return function(string $environment = 'development') {
    $config = [];

    switch ($environment) {
        case 'development':
            $config['db.host'] = 'postgres';
            $config['db.name'] = 'archdemo';
            $config['db.user'] = 'postgres';
            $config['db.pass'] = '';
            $config['db.driver'] = 'pdo_pgsql';
            break;
        case 'test':
            // handle CI database hose for test environment
            $testHost = getenv('DB_HOST');
            if (!$testHost) {
                $testHost = 'postgres';
            }
            $config['db.host'] = $testHost;
            $config['db.name'] = 'archdemo_test';
            $config['db.user'] = 'postgres';
            $config['db.pass'] = '';
            $config['db.driver'] = 'pdo_pgsql';
            break;
        case 'production':
            $config['db.host'] = getenv('DB_HOST');
            $config['db.name'] = getenv('DB_NAME');
            $config['db.user'] = getenv('DB_USER');
            $config['db.pass'] = getenv('DB_PASS');
            $config['db.driver'] = getenv('DB_DRIVER');
            break;
    }

    return $config;
};