<?php

$env = @file_get_contents('.env');
if ($env) {
  foreach (explode("\n", $env) as $line) {
    if (trim($line) == '' || str_starts_with($line, '#')) {
      continue;
    }
    list($key, $value) = explode('=', $line, 2);
    define($key, $value);
  }
} else {
  if (getenv('TRANSLOADIT_KEY')) {
    define('TRANSLOADIT_KEY', getenv('TRANSLOADIT_KEY'));
  }
  if (getenv('TRANSLOADIT_SECRET')) {
    define('TRANSLOADIT_SECRET', getenv('TRANSLOADIT_SECRET'));
  }
}
