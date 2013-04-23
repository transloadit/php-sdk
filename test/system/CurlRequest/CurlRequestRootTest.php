<?php

use transloadit\CurlRequest;

class CurlRequestRootTest extends \PHPUnit_Framework_TestCase{
  public function testRoot() {
    $request = new CurlRequest();
    $request->url = 'http://api2.transloadit.com/';
    $request->method = 'GET';
    $response = $request->execute();

    $this->assertContains('"ok"', $response->data);
  }
}
?>
