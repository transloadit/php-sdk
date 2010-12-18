<?php
require_once(dirname(dirname(__FILE__)).'/BaseTestCase.php');
require_once(TEST_LIB_DIR.'/CurlResponse.php');

class CurlResponseTest extends BaseTestCase{
  public function setUp() {
    $this->response = new CurlResponse();
  }

  public function testAttributes() {
    $this->assertEquals(null, $this->response->data);
    $this->assertEquals(null, $this->response->curlErrorNumber);
    $this->assertEquals(null, $this->response->curlErrorMessage);
    $this->assertEquals(null, $this->response->curlInfo);
  }

  public function testConstructor() {
    $transloadit = new CurlResponse(array('foo' => 'bar'));
    $this->assertEquals('bar', $transloadit->foo);
  }

  public function testParseJson() {
    $data = array('foo' => 'bar');

    $this->response->data = json_encode($data);
    $r = $this->response->parseJson();

    $this->assertEquals(true, $r);
    $this->assertEquals($data, $this->response->data);

    $data = $this->response->data = 'no json';
    $r = $this->response->parseJson();

    $this->assertEquals(false, $r);
    $this->assertEquals($data, $this->response->data);
  }

  public function testError() {
    $error = $this->response->error();
    $this->assertEquals(false, $error);

    $number = $this->response->curlErrorNumber = 27;
    $message = $this->response->curlErrorMessage = 'Something went wrong';
    $error = $this->response->error();
    $this->assertEquals(sprintf('curl: %d: %s', $number, $message), $error);
  }
}
?>
