<?php

class TransloaditRequestRootTest extends SystemTestCase{
  public function testRoot() {
    $this->request->setMethodAndPath('GET', '/');
    $response = $this->request->execute();

    $this->assertEquals($this->request->headers[1], 'Transloadit-Client: php-sdk:2.1.0');
    $this->assertEquals(true, isset($response->data['ok']));
    $this->assertInstanceOf('transloadit\TransloaditResponse', $response);
  }
}
