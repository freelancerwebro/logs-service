<?php

use Symfony\Component\Dotenv\Dotenv;

require dirname(__DIR__).'/vendor/autoload.php';

if (method_exists(Dotenv::class, 'bootEnv')) {
    (new Dotenv())->bootEnv(dirname(__DIR__).'/.env.test');
}

if (isset($_ENV['BOOTSTRAP_CLEAR_CACHE_ENV'])) {
    // executes the "php bin/console cache:clear" command
    passthru(sprintf(
        'php "%s/../bin/console" cache:clear --env=%s --no-warmup',
        __DIR__,
        $_ENV['BOOTSTRAP_CLEAR_CACHE_ENV']
    ));
}

if (isset($_ENV['BOOTSTRAP_RESET_DATABASE']) && true == $_ENV['BOOTSTRAP_RESET_DATABASE']) {
    echo 'Resetting test database...';
    passthru(sprintf(
        'php "%s/../bin/console" doctrine:schema:drop --env=test --force --no-interaction',
        __DIR__
    ));
    passthru(sprintf(
        'php "%s/../bin/console" doctrine:schema:update --env=test --force --no-interaction',
        __DIR__
    ));
    passthru(sprintf(
        'php "%s/../bin/console" doctrine:fixtures:load --env=test --no-interaction',
        __DIR__
    ));
    echo ' Done'.PHP_EOL.PHP_EOL;
}

if ($_SERVER['APP_DEBUG']) {
    umask(0000);
}
