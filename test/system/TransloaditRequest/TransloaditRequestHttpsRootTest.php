<?php

namespace transloadit\test\system\TransloaditRequest;

class TransloaditRequestHttpsRootTest extends \transloadit\test\SystemTestCase {
  public function testRoot() {
    $this->request->protocol = 'https';
    $this->request->setMethodAndPath('GET', '/');
    $response = $this->request->execute();

    $this->assertEquals(true, array_key_exists('ok', $response->data));
    $this->assertInstanceOf('transloadit\TransloaditResponse', $response);
  }
}
