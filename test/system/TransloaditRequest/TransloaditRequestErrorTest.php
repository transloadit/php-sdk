<?php

class TransloaditRequestErrorTest extends SystemTestCase{
  public function testRoot() {
    $this->request->setMethodAndPath('POST', '/assemblies');
    $response = $this->request->execute();

    $error = $response->error();
    $this->assertStringContainsString('transloadit', $error);
    $this->assertStringContainsString('STEPS', $error);
  }
}
