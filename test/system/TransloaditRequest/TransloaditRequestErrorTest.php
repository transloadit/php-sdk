<?php

namespace transloadit\test\system\TransloaditRequest;

class TransloaditRequestErrorTest extends \transloadit\test\SystemTestCase {
  public function testRoot() {
    $this->request->setMethodAndPath('POST', '/assemblies');
    $response = $this->request->execute();

    $error = $response->error();
    $this->assertStringContainsString('transloadit', $error);
    $this->assertStringContainsString('STEPS', $error);
  }
}
