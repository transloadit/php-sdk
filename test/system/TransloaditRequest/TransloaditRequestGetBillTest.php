<?php

namespace transloadit\test\system\TransloaditRequest;

class TransloaditRequestGetBillTest extends \transloadit\test\SystemTestCase {
  public function testRoot() {
    $this->request->setMethodAndPath('GET', '/bill/' . date('Y-m'));
    $response = $this->request->execute();

    if (isset($response->data['ok'])) {
      $this->assertStringContainsString('BILL', $response->data['ok']);
      return;
    }

    $this->assertArrayHasKey(
      'error',
      $response->data,
      'Bill response should include ok or error field'
    );
    $this->assertStringContainsString('BILL', (string) $response->data['error']);
  }
}
