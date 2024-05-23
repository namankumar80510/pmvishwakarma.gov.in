#!/usr/bin/env php
<?php

use Dikki\DotEnv\DotEnv;
use Nette\Utils\Finder;
use Symfony\Component\Console\Application;

if (PHP_SAPI !== 'cli') {
    exit('Script needs to be run from Command Line Interface (cli)');
}

const DS = DIRECTORY_SEPARATOR;
const ROOT_DIR = __DIR__ . DS;

require_once __DIR__ . '/vendor/autoload.php';

(new DotEnv(__DIR__))->load();

if (getenv('APP_ENV')==='dev') {
    define("BUILD_DIR", ROOT_DIR . 'dev-build' . DS);
} else {
    define("BUILD_DIR", ROOT_DIR . 'build' . DS);
}

$app = new Application("Console App");

$commands = Finder::findFiles('*Command.php')
    ->from(__DIR__ . '/src/Commands');

foreach ($commands as $command) {
    $command = "\App\Commands\\" . $command->getBasename('.php');
    $app->add(new $command);
}

try {
    $app->run();
} catch (Exception $e) {
    die ($e->getMessage());
}