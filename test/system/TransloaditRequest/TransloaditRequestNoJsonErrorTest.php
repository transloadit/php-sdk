<?php
require_once(dirname(__FILE__).'/TransloaditRequestTestCase.php');

class TransloaditRequestNoJsonErrorTest extends TransloaditRequestTestCase{
  public function testRoot() {
    $this->request->url = 'http://google.com/';
    $response = $this->request->execute();

    $error = $response->error();
    $this->assertContains('no json', $error);
  }
}
