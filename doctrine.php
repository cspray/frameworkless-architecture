<?php declare(strict_types=1);

require_once __DIR__ . '/vendor/autoload.php';

use Symfony\Component\Console\Helper\HelperSet;

$environment = $_ENV['APP_ENV'] ?? 'development';
$injector = \Cspray\ArchDemo\bootstrap($environment);

$entityManager = $injector->make(\Doctrine\ORM\EntityManagerInterface::class);

$helperSet = new HelperSet([
    'db' => new \Doctrine\DBAL\Tools\Console\Helper\ConnectionHelper($entityManager->getConnection()),
    'em' => new \Doctrine\ORM\Tools\Console\Helper\EntityManagerHelper($entityManager),
    'dialog' => new \Symfony\Component\Console\Helper\QuestionHelper()
]);

$cli = \Doctrine\ORM\Tools\Console\ConsoleRunner::createApplication($helperSet);

$cli->addCommands([
    new \Doctrine\DBAL\Migrations\Tools\Console\Command\DiffCommand(),
    new \Doctrine\DBAL\Migrations\Tools\Console\Command\ExecuteCommand(),
    new \Doctrine\DBAL\Migrations\Tools\Console\Command\GenerateCommand(),
    new \Doctrine\DBAL\Migrations\Tools\Console\Command\MigrateCommand(),
    new \Doctrine\DBAL\Migrations\Tools\Console\Command\StatusCommand(),
    new \Doctrine\DBAL\Migrations\Tools\Console\Command\VersionCommand()
]);

$cli->run();