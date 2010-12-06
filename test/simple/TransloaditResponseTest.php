<?php
require_once(dirname(dirname(__FILE__)).'/BaseTestCase.php');
require_once(TEST_LIB_DIR.'/TransloaditResponse.php');

class TransloaditResponseTest extends BaseTestCase{
  public function setUp() {
    $this->response = new TransloaditResponse();
  }

  public function testConstructor() {
    $parent = get_parent_class($this->response);
    $this->assertEquals('CurlResponse', $parent);
  }

  public function testError() {
    $this->response->data = array('ok' => 'ASSEMBLY_DOING_SOMETHING');
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
    $this->assertContains('curl', $error);
  }
}
?>
