#!/usr/bin/env php
<?php
// application.php

chdir(__DIR__);

require __DIR__ . '/vendor/autoload.php';

$loader = new \Composer\Autoload\ClassLoader();
$loader->add('commands', __DIR__ . '/commands');
$loader->register();
$loader->setUseIncludePath(true);

use Dotenv\Dotenv;
use Symfony\Component\Console\Application;

define('BASE_PATH', __DIR__);

$dotenv = new Dotenv(BASE_PATH . '/config');
$dotenv->load();

$application = new Application();

$application->add(new commands\BuildIOSBetaCommand());
$application->setDefaultCommand('build-ios-beta');

$application->run();