<?php

class TransloaditRequestGetBillTest extends SystemTestCase{
  public function testRoot() {
    $this->request->setMethodAndPath('GET', '/bill/'.date('Y-m'));
    $response = $this->request->execute();

    $this->assertStringContainsString('BILL', $response->data['ok']);
  }
}
