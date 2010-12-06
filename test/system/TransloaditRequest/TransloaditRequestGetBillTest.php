<?php
require_once(dirname(__FILE__).'/TransloaditRequestTestCase.php');

class TransloaditRequestGetBillTest extends TransloaditRequestTestCase{
  public function testRoot() {
    $this->request->init('GET', '/bill/'.date('Y-m'));
    $response = $this->request->execute();

    $this->assertContains('BILL', $response->data['ok']);
  }
}
