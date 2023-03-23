<?php

$autoloader = dirname(dirname(__DIR__)) . '/vendor/autoload.php';

if (!file_exists($autoloader)) {
  throw new \Exception(
    "composer autoload not found, run composer install first"
  );
}

require $autoloader;
