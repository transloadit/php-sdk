<?php

if (getenv('TEST_ACCOUNT_KEY'))
  define('TEST_ACCOUNT_KEY', getenv('TEST_ACCOUNT_KEY'));
if (getenv('TEST_ACCOUNT_SECRET'))
  define('TEST_ACCOUNT_SECRET', getenv('TEST_ACCOUNT_SECRET'));
