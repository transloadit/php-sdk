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
  if (getenv('TEST_ACCOUNT_KEY'))
    define('TEST_ACCOUNT_KEY', getenv('TEST_ACCOUNT_KEY'));
  if (getenv('TEST_ACCOUNT_SECRET'))
    define('TEST_ACCOUNT_SECRET', getenv('TEST_ACCOUNT_SECRET'));
}
