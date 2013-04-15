<?php

class TransloaditRequestRootTest extends TransloaditRequestTestCase{
  public function testRoot() {
    $this->request->setMethodAndPath('GET', '/');
    $response = $this->request->execute();

    $this->assertEquals(true, array_key_exists('ok', $response->data));
    $this->assertEquals('TransloaditResponse', get_class($response));
  }
}
