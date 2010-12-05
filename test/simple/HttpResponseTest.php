<?php
require_once(dirname(dirname(__FILE__)).'/TransloaditTestCase.php');
require_once(TEST_LIB_DIR.'/HttpResponse.php');

class HttpResponseTest extends TransloaditTestCase{
  public function setUp() {
    $this->response = new HttpResponse();
  }

  //private function _mock() {
    //$methods = func_get_args();
    //$this->response = $this->getMock('HttpResponse', $methods);
  //}

  public function testAttributes() {
    $this->assertEquals(null, $this->response->data);
    $this->assertEquals(null, $this->response->curlErrorNumber);
    $this->assertEquals(null, $this->response->curlErrorMessage);
    $this->assertEquals(null, $this->response->curlInfo);
  }

  public function testConstructor() {
    $transloadit = new HttpResponse(array('foo' => 'bar'));
    $this->assertEquals('bar', $transloadit->foo);
  }

  public function testParseJson() {
    $data = array('foo' => 'bar');

    $this->response->data = json_encode($data);
    $this->response->parseJson();

    $this->assertEquals($data, $this->response->data);
  }
}
?>
