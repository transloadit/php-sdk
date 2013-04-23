<?php

class TransloaditRequestGetBillTest extends SystemTestCase{
  public function testRoot() {
    $this->request->setMethodAndPath('GET', '/bill/'.date('Y-m'));
    $response = $this->request->execute();

    $this->assertContains('BILL', $response->data['ok']);
  }
}
