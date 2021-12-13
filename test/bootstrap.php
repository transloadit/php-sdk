<?php

require dirname(__DIR__) . "/vendor/autoload.php";

if (file_exists(__DIR__ . '/config.php')) {
    require __DIR__ . '/config.php';
}

define('TEST_FIXTURE_DIR', __DIR__ . '/fixture');

abstract class SystemTestCase extends PHPUnit\Framework\TestCase{
  public function setUp(): void {
    if (!defined('TEST_ACCOUNT_KEY') || !defined('TEST_ACCOUNT_SECRET')) {
      $this->markTestSkipped(
        'Have a look at test/config.php.template to get this test to run.'
      );
      return;
    }

    // @todo Load config from git excluded config file
    $this->request = new transloadit\TransloaditRequest(array(
      'key' => TEST_ACCOUNT_KEY,
      'secret' => TEST_ACCOUNT_SECRET,
    ));
  }
}

class TransloaditRequestTestCase extends PHPUnit\Framework\TestCase{
  public function setUp(): void {
    if (!defined('TEST_ACCOUNT_KEY') || !defined('TEST_ACCOUNT_SECRET')) {
      $this->markTestSkipped(
        'Have a look at test/config.php.template to get this test to run.'
      );
      return;
    }

    // @todo Load config from git excluded config file
    $this->transloadit = new transloadit\Transloadit(array(
      'key' => TEST_ACCOUNT_KEY,
      'secret' => TEST_ACCOUNT_SECRET,
    ));
  }
}
