<?php

namespace transloadit\test\system\TransloaditRequest;

class TransloaditRequestGetBillTest extends \transloadit\test\SystemTestCase {
  public function testRoot() {
    $this->request->setMethodAndPath('GET', '/bill/' . date('Y-m'));
    $response = $this->request->execute();

    $this->assertStringContainsString('BILL', $response->data['ok']);
  }
}
