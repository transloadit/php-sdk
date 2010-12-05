<?php
require_once(dirname(dirname(__FILE__)).'/TransloaditTestCase.php');
require_once(TEST_LIB_DIR.'/HttpRequest.php');

class HttpRequestTest extends TransloaditTestCase{
  public function setUp() {
    $this->request = new HttpRequest();
  }

  private function _mock() {
    $methods = func_get_args();
    $this->request = $this->getMock('HttpRequest', $methods);
  }

  public function testAttributes() {
    $this->assertEquals('GET', $this->request->method);
    $this->assertEquals(null, $this->request->url);
    $this->assertEquals(array(), $this->request->headers);
    $this->assertEquals(array(), $this->request->fields);
  }

  public function testConstructor() {
    $transloadit = new HttpRequest(array('foo' => 'bar'));
    $this->assertEquals('bar', $transloadit->foo);
  }

  public function testSetField() {
    $this->request->setField('foo', 'bar');
    $this->assertEquals('bar', $this->request->fields['foo']);
  }

  public function testSetFile() {
    $this->_mock('setField');

    $this->request
      ->expects($this->once())
      ->method('setField')
      ->with($this->equalTo('foo'), $this->equalTo('@/my/file.dat'));
    $this->request->setFile('foo', '/my/file.dat');
  }

  public function testAddFile() {
    $this->request->addFile('/my/file1.dat');
    $this->assertEquals('@/my/file1.dat', $this->request->fields['file_1']);

    $this->request->addFile('/my/file2.dat');
    $this->assertEquals('@/my/file2.dat', $this->request->fields['file_2']);

    $this->request->setField('foo', 'bar');
    $this->request->addFile('/my/file3.dat');
    $this->assertEquals('@/my/file3.dat', $this->request->fields['file_3']);
  }

  public function testGetCurlOptions() {
    // test return transfer
    $options = $this->request->getCurlOptions();
    $this->assertEquals(true, $options[CURLOPT_RETURNTRANSFER]);

    // test method
    $this->request->method = 'something';
    $options = $this->request->getCurlOptions();
    $this->assertEquals($this->request->method, $options[CURLOPT_CUSTOMREQUEST]);

    // test url
    $this->request->url = 'http://foo.com/bar';
    $options = $this->request->getCurlOptions();
    $this->assertEquals($this->request->url, $options[CURLOPT_URL]);

    // test headers
    $this->request->headers = array('Foo: bar');
    $options = $this->request->getCurlOptions();
    $this->assertEquals($this->request->headers, $options[CURLOPT_HTTPHEADER]);

    // test post fields
    $this->request->fields = array('hello' => 'world');
    $options = $this->request->getCurlOptions();
    $this->assertEquals($this->request->fields, $options[CURLOPT_POSTFIELDS]);
  }

  public function testExecute() {
    // Can't test this method because PHP doesn't allow stubbing the calls
    // to curl easily. However, the method hardly contains any logic as all
    // of that is located in other methods.
  }
}
?>
