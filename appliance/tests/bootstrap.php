<?php

declare(strict_types=1);

use Symfony\Component\Dotenv\Dotenv;

include __DIR__ . '/fakeQuery.php';
include __DIR__ . '/fakeUOW.php';

require dirname(__DIR__) . '/vendor/autoload.php';

if (file_exists(dirname(__DIR__) . '/config/bootstrap.php')) {
    require dirname(__DIR__) . '/config/bootstrap.php';
} elseif (method_exists(Dotenv::class, 'bootEnv')) {
    (new Dotenv())->bootEnv(dirname(__DIR__) . '/.env');
}

date_default_timezone_set('UTC');

ini_set('memory_limit', '384M');

error_reporting(E_ALL | E_STRICT);
