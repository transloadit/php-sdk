<?php

$env = @file_get_contents(dirname(__FILE__) . '/../.env');
if ($env) {
  foreach (explode("\n", $env) as $line) {
    if (trim($line) == '' || str_starts_with($line, '#')) {
      continue;
    }
    list($key, $value) = explode('=', $line, 2);
    $key = str_replace('export ', '', $key);
    $value = str_replace('"', '', str_replace("'", '', $value));

    define($key, $value);
    putenv("$key=$value");
  }
} else {
  if (getenv('TRANSLOADIT_KEY') && getenv('TRANSLOADIT_SECRET')) {
    define('TRANSLOADIT_KEY', getenv('TRANSLOADIT_KEY'));
    define('TRANSLOADIT_SECRET', getenv('TRANSLOADIT_SECRET'));
  }
}
