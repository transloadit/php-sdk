<?php

class TransloaditRequestRootTest extends SystemTestCase {
  public function testRoot() {
    $this->request->setMethodAndPath('GET', '/');
    $response = $this->request->execute();

    $this->assertStringStartsWith('Transloadit-Client: php-sdk:', $this->request->headers[1]);
    $this->assertEquals(true, isset($response->data['ok']));
    $this->assertInstanceOf('transloadit\TransloaditResponse', $response);
  }
}
