<?php declare(strict_types=1);

return function(string $environment) {
    $config = [];

    switch ($environment) {
        case 'development':
            $config['db.host'] = 'dev-host';
            $config['db.name'] = 'dev-name';
            $config['db.user'] = 'dev-user';
            $config['db.pass'] = 'dev-pass';
            $config['db.driver'] = 'dev-driver';
            break;
        case 'test':
            $config['db.host'] = 'test-host';
            $config['db.host'] = 'test-host';
            $config['db.name'] = 'test-name';
            $config['db.user'] = 'test-user';
            $config['db.pass'] = 'test-pass';
            $config['db.driver'] = 'test-driver';
            break;
    }

    return $config;
};