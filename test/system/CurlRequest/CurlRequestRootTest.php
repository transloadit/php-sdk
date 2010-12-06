<?php
require_once(dirname(dirname(dirname(__FILE__))).'/BaseTestCase.php');
require_once(TEST_LIB_DIR.'/CurlRequest.php');

class CurlRequestRootTest extends BaseTestCase{
  public function testRoot() {
    $request = new CurlRequest();
    $request->url = 'http://api2.transloadit.com/';
    $request->method = 'GET';
    $response = $request->execute();

    $this->assertContains('"ok"', $response->data);
  }
}
?>
