<?php

namespace transloadit\test\system\TransloaditRequest;

class TransloaditRequestNoJsonErrorTest extends \transloadit\test\SystemTestCase {
  public function testRoot() {
    $this->request->url = 'http://google.com/';
    $response = $this->request->execute();

    $error = $response->error();
    $this->assertStringContainsString('no json', $error);
  }
}
