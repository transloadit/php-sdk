<?php
require_once(dirname(dirname(dirname(__FILE__))).'/TransloaditTestCase.php');
require_once(TEST_LIB_DIR.'/HttpRequest.php');

class HttpRequestRootTest extends PHPUnit_Framework_TestCase{
  public function testRoot() {
    $request = new HttpRequest();
    $request->url = 'http://api2.transloadit.com/';
    $request->method = 'GET';
    $response = $request->execute();

    $this->assertContains('"ok"', $response->data);
  }
}
?>
