<?php

class TransloaditRequestRootTest extends SystemTestCase{
  public function testRoot() {
    $this->request->setMethodAndPath('GET', '/');
    $response = $this->request->execute();

    $this->assertEquals(true, isset($response->data['ok']));
    $this->assertInstanceOf('transloadit\TransloaditResponse', $response);
  }
}
