<?php

class TransloaditRequestNoJsonErrorTest extends TransloaditRequestTestCase{
  public function testRoot() {
    $this->request->url = 'http://google.com/';
    $response = $this->request->execute();

    $error = $response->error();
    $this->assertContains('no json', $error);
  }
}
