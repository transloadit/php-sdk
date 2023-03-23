<?php

class TransloaditRequestNoJsonErrorTest extends SystemTestCase {
  public function testRoot() {
    $this->request->url = 'http://google.com/';
    $response = $this->request->execute();

    $error = $response->error();
    $this->assertStringContainsString('no json', $error);
  }
}
