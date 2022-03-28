<?php

use transloadit\CurlRequest;

class CurlRequestTest extends PHPUnit\Framework\TestCase{
  public function setUp(): void {
    $this->request = new CurlRequest();
  }

  public function testAttributes() {
    $this->assertEquals('GET', $this->request->method);
    $this->assertEquals(null, $this->request->url);
    $this->assertEquals([], $this->request->headers);
    $this->assertEquals([], $this->request->fields);
    $this->assertEquals([], $this->request->files);
  }

  public function testConstructor() {
    $request = new CurlRequest(['foo' => 'bar']);
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
    $this->request->headers = ['Foo: bar'];
    $options = $this->request->getCurlOptions();
    $this->assertEquals($this->request->headers, $options[CURLOPT_HTTPHEADER]);

    // test put fields
    $this->request->fields = ['hello' => 'world'];
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

    $fixture = dirname(dirname(__FILE__)) . '/fixture/image-resize-robot.jpg';

    // test post files
    $this->request->method = 'POST';
    $this->request->fields = ['super' => 'cool'];
    $this->request->files = ['foo' => $fixture];
    $options = $this->request->getCurlOptions();

    // -- Start edit --
    // Edit by Aart Berkhout involving issue #8: CURL depricated functions (PHP 5.5)
    // https://github.com/transloadit/php-sdk/issues/8
    $filesOptions = function_exists('curl_file_create') ? 
      ['foo' => curl_file_create($this->request->files['foo'])] :
      ['foo' => '@'.$this->request->files['foo']];

    $this->assertEquals(
      array_merge(
        $this->request->fields,
        $filesOptions
      ),
      $options[CURLOPT_POSTFIELDS]
    );

    // test file numbering
    $this->request->files = [$fixture];
    $options = $this->request->getCurlOptions();

    $filesOptions = function_exists('curl_file_create') ? 
      ['file_1' => curl_file_create($this->request->files[0])] :
      ['file_1' => '@'.$this->request->files[0]];

    $this->assertEquals(
      array_merge(
        $this->request->fields,
        $filesOptions
      ),
      $options[CURLOPT_POSTFIELDS]
    );
    // -- End edit --

  }

  public function testExecute() {
    // Can't test this method because PHP doesn't allow stubbing the calls
    // to curl easily. However, the method hardly contains any logic as all
    // of that is located in other methods.
  }
}
?>
