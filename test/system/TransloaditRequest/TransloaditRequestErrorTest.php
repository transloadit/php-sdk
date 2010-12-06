<?php
require_once(dirname(__FILE__).'/TransloaditRequestTestCase.php');

class TransloaditRequestErrorTest extends TransloaditRequestTestCase{
  public function testRoot() {
    $this->request->setMethodAndPath('POST', '/assemblies');
    $response = $this->request->execute();

    $error = $response->error();
    $this->assertContains('transloadit', $error);
    $this->assertContains('STEPS', $error);
  }
}
