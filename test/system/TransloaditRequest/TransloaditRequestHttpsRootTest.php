<?php

class TransloaditRequestHttpsRootTest extends TransloaditRequestTestCase{
  public function testRoot() {
    $this->request->protocol = 'https';
    $this->request->setMethodAndPath('GET', '/');
    $response = $this->request->execute();

    $this->assertEquals(true, array_key_exists('ok', $response->data));
    $this->assertEquals('TransloaditResponse', get_class($response));
  }
}
