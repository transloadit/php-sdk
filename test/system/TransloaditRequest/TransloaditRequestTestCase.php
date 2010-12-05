<?php
require_once(dirname(dirname(dirname(__FILE__))).'/TransloaditTestCase.php');
require_once(TEST_LIB_DIR.'/TransloaditRequest.php');

abstract class TransloaditRequestTestCase extends TransloaditTestCase{
  public function setUp() {
    if (!defined('TEST_ACCOUNT_KEY')) {
      $this->markTestSkipped(
        'Have a look at test/config.php.template to get this test to run.'
      );
      return;
    }

    // @todo Load config from git excluded config file
    $this->request = new TransloaditRequest(array(
      'key' => TEST_ACCOUNT_KEY,
      'secret' => TEST_ACCOUNT_SECRET,
    ));
  }
}
