<?php

use transloadit\CurlRequest;

class CurlRequestRootTest extends \PHPUnit\Framework\TestCase {
  public function testRoot() {
    $request = new CurlRequest();
    $request->url = 'http://api2.transloadit.com/';
    $request->method = 'GET';
    $response = $request->execute();
    $this->assertStringContainsString('"ok"', $response->data);
  }
}
