<?php
date_default_timezone_set('UTC');
use transloadit\Transloadit;
use transloadit\TransloaditResponse;

class TransloaditTest extends \PHPUnit\Framework\TestCase{
  public function setUp(): void {
    $this->transloadit = new Transloadit();
  }

  public function testConstructor() {
    $transloadit = new Transloadit(array('foo' => 'bar'));
    $this->assertEquals('bar', $transloadit->foo);
  }

  public function testAttributes() {
    $this->assertEquals($this->transloadit->key, null);
    $this->assertEquals($this->transloadit->secret, null);
  }

  public function testCreateAssembly() {
    $transloadit = $this->getMockBuilder(Transloadit::class)
                        ->setMethods(['request'])
                        ->getMock();
    $assembly = $this->getMockBuilder(TransloaditResponse::class)
                     ->getMock();

    $options = array('foo' => 'bar');

    $transloadit
      ->expects($this->at(0))
      ->method('request')
      ->with($this->equalTo($options + array(
        'method'   => 'POST',
        'path'     => '/assemblies',
      )))
      ->willReturn($assembly);

    $transloadit->createAssembly($options);
  }

  public function testRequest() {
    $this->transloadit->key = 'my-key';
    $this->transloadit->secret = 'my-secret';
    $request = $this->transloadit->request(array('foo' => 'bar'), false);

    $this->assertEquals($this->transloadit->key, $request->key);
    $this->assertEquals($this->transloadit->secret, $request->secret);
    $this->assertEquals('bar', $request->foo);

    // Unfortunately we can't test the $execute parameter because PHP
    // is a little annoying. But that's ok for now.
  }

  public function testResponse() {
    $response = Transloadit::response();
    $this->assertEquals(false, $response);

    $data = array('foo' => 'bar');
    $_POST['transloadit'] = json_encode($data);
    $response = Transloadit::response();
    $this->assertInstanceOf(TransloaditResponse::class, $response);
    $this->assertEquals($data, $response->data);


    // Can't really test the $_GET['assembly_url'] case because of PHP for now.
  }

  public function testCreateAssemblyForm() {
    $transloadit = $this->getMockBuilder(Transloadit::class)
                        ->setMethods(['request'])
                        ->getMock();
    $assembly = $this->getMockBuilder(TransloaditResponse::class)
                     ->setMethods(['prepare'])
                     ->getMock();

    $assembly->method = 'ROCK';
    $assembly->url = 'http://api999.transloadit.com/assemblies';
    $assembly->fields = array(
      'foo' => 'bar"bar',
      'hey' => 'you',
    );
    $options = array('foo' => 'bar');

    $transloadit
      ->expects($this->at(0))
      ->method('request')
      ->with($this->equalTo($options + array(
        'method' => 'POST',
        'path' => '/assemblies',
      )), $this->equalTo(false))
      ->willReturn($assembly);

    $assembly
      ->expects($this->at(0))
      ->method('prepare');

    $options['attributes'] = array('class' => 'nice');
    $tags = explode("\n", $transloadit->createAssemblyForm($options));

    $formTag = array_shift($tags);
    $this->assertTrue(preg_match('/action="http:\/\/api999\.transloadit\.com\/assemblies"/', $formTag) !== false);
    $this->assertTrue(preg_match('/method="ROCK"/', $formTag) !== false);
    $this->assertTrue(preg_match('/enctype="multipart\/form-data"/', $formTag) !== false);
    $this->assertTrue(preg_match('/class="nice"/', $formTag) !== false);

    foreach ($assembly->fields as $field => $val) {
      $inputTag = array_shift($tags);
      $this->assertTrue(preg_match('/type="hidden"/', $inputTag) !== false);
      $this->assertTrue(preg_match('/name="'.$field.'"/', $inputTag) !== false);
      $this->assertTrue(preg_match('/value="'.$val.'"/', $inputTag) !== false);
    }
  }
}
