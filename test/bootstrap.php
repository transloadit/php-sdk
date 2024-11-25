<?php

namespace transloadit\test;

require dirname(__DIR__) . "/vendor/autoload.php";

if (file_exists(__DIR__ . '/config.php')) {
  require __DIR__ . '/config.php';
}

define('TEST_FIXTURE_DIR', __DIR__ . '/fixture');

abstract class SystemTestCase extends \PHPUnit\Framework\TestCase {
  protected \transloadit\TransloaditRequest $request;

  public function setUp(): void {
    if (!defined('TRANSLOADIT_KEY') || !defined('TRANSLOADIT_SECRET')) {
      $this->markTestSkipped(
        'Have a look at test/config.php to get this test to run.'
      );
      return;
    }

    $this->request = new \transloadit\TransloaditRequest([
      'key' => TRANSLOADIT_KEY,
      'secret' => TRANSLOADIT_SECRET,
    ]);
  }
}

class TransloaditRequestTestCase extends \PHPUnit\Framework\TestCase {
  protected \transloadit\Transloadit $transloadit;

  public function setUp(): void {
    if (!defined('TRANSLOADIT_KEY') || !defined('TRANSLOADIT_SECRET')) {
      $this->markTestSkipped(
        'Have a look at test/config.php to get this test to run.'
      );
      return;
    }

    $this->transloadit = new \transloadit\Transloadit([
      'key' => TRANSLOADIT_KEY,
      'secret' => TRANSLOADIT_SECRET,
    ]);
  }
}
