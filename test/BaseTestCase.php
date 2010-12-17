<?php
define('TEST_ROOT_DIR', dirname(dirname(__FILE__)));
define('TEST_LIB_DIR', TEST_ROOT_DIR.'/lib/Transloadit');
define('TEST_TEST_DIR', TEST_ROOT_DIR.'/test');
define('TEST_FIXTURE_DIR', TEST_TEST_DIR.'/fixture');

@include_once(TEST_TEST_DIR.'/config.php');

abstract class BaseTestCase extends PHPUnit_Framework_TestCase{
}
