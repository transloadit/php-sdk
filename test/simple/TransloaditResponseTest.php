<?php

namespace transloadit\test\simple;

use transloadit\TransloaditResponse;
use transloadit\CurlResponse;

class TransloaditResponseTest extends \PHPUnit\Framework\TestCase {
  private TransloaditResponse $response;

  public function setUp(): void {
    $this->response = new TransloaditResponse();
  }

  public function testConstructor() {
    $this->assertInstanceOf('transloadit\\CurlResponse', $this->response);
  }

  public function testError() {
    $this->response->data = 'no json';
    $error = $this->response->error();
    $this->assertEquals(
      sprintf('transloadit: bad response, no json: ' . $this->response->data),
      $error
    );

    $this->response->data = ['ok' => 'ASSEMBLY_DOING_SOMETHING'];
    $error = $this->response->error();
    $this->assertEquals(false, $error);

    unset($this->response->data['ok']);
    $error = $this->response->error();
    $this->assertEquals(
      sprintf('transloadit: bad response data, no ok / error key included.'),
      $error
    );

    $ERROR = 'ASSEMBLY_WENT_TOTALLY_BAD';
    $this->response->data['error'] = $ERROR;
    $error = $this->response->error();
    $this->assertEquals(
      sprintf('transloadit: %s', $ERROR),
      $error
    );

    $MESSAGE = 'Something went awefully wrong!';
    $this->response->data['message'] = $MESSAGE;
    $error = $this->response->error();
    $this->assertEquals(
      sprintf('transloadit: %s: %s', $ERROR, $MESSAGE),
      $error
    );

    $REASON = 'Something went awefully wrong!';
    $this->response->data['reason'] = $REASON;
    $error = $this->response->error();
    $this->assertEquals(
      sprintf('transloadit: %s: %s: %s', $ERROR, $MESSAGE, $REASON),
      $error
    );

    $this->response->curlErrorNumber = 27;
    $error = $this->response->error();
    $this->assertStringContainsString('curl', $error);
  }
}
