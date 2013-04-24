<?php
$autoloader = dirname(dirname(__DIR__)) . '/vendor/autoload.php';
$config = __DIR__ . '/config.php';

if (!file_exists($autoloader)) {
    throw new \Exception(
        "composer autoload not found, run composer install first"
    );
}
if (!file_exists($config)) {
    throw new \Exception(
        "Please check example/config/config.php.template for running the examples"
    );
}

require $autoloader;
return require $config;
