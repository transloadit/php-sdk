<?php
require_once(dirname(dirname(__FILE__)).'/TransloaditTestCase.php');
require_once(TEST_LIB_DIR.'/CurlRequest.php');

class CurlRequestTest extends TransloaditTestCase{
  public function setUp() {
    $this->request = new CurlRequest();
  }

  private function _mock() {
    $methods = func_get_args();
    $this->request = $this->getMock('CurlRequest', $methods);
  }

  public function testAttributes() {
    $this->assertEquals('GET', $this->request->method);
    $this->assertEquals(null, $this->request->url);
    $this->assertEquals(array(), $this->request->headers);
    $this->assertEquals(array(), $this->request->fields);
    $this->assertEquals(array(), $this->request->files);
  }

  public function testConstructor() {
    $request = new CurlRequest(array('foo' => 'bar'));
    $this->assertEquals('bar', $request->foo);
  }

  public function testGetCurlOptions() {
    // test return transfer
    $options = $this->request->getCurlOptions();
    $this->assertEquals(true, $options[CURLOPT_RETURNTRANSFER]);

    // test method
    $this->request->method = 'PUT';
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

    // test put fields
    $this->request->fields = array('hello' => 'world');
    $options = $this->request->getCurlOptions();
    $this->assertEquals($this->request->fields, $options[CURLOPT_POSTFIELDS]);

    // test post fields
    $this->request->method = 'POST';
    $options = $this->request->getCurlOptions();
    $this->assertEquals($this->request->fields, $options[CURLOPT_POSTFIELDS]);
    $this->assertEquals($this->request->url, $options[CURLOPT_URL]);

    // test get query
    $this->request->method = 'GET';
    $options = $this->request->getCurlOptions();
    $this->assertEquals(
      $this->request->url.'?'.http_build_query($this->request->fields),
      $options[CURLOPT_URL]);
    $this->assertArrayNotHasKey(CURLOPT_POSTFIELDS, $options);

    // test post files
    $this->request->method = 'POST';
    $this->request->fields = array('super' => 'cool');
    $this->request->files = array('foo' => '/my/file.dat');
    $options = $this->request->getCurlOptions();
    $this->assertEquals(
      array_merge(
        $this->request->fields,
        array('foo' => '@'.$this->request->files['foo'])
      ),
      $options[CURLOPT_POSTFIELDS]
    );

    // test file numbering
    $this->request->files = array('/my/file.dat');
    $options = $this->request->getCurlOptions();
    $this->assertEquals(
      array_merge(
        $this->request->fields,
        array('file_1' => '@'.$this->request->files[0])
      ),
      $options[CURLOPT_POSTFIELDS]
    );
  }

  public function testExecute() {
    // Can't test this method because PHP doesn't allow stubbing the calls
    // to curl easily. However, the method hardly contains any logic as all
    // of that is located in other methods.
  }
}
?>
