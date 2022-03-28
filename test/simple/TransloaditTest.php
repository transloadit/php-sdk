<?php
date_default_timezone_set('UTC');
use transloadit\Transloadit;
use transloadit\TransloaditResponse;

class TransloaditTest extends \PHPUnit\Framework\TestCase{
  public function setUp(): void {
    $this->transloadit = new Transloadit();
  }

  public function testConstructor() {
    $transloadit = new Transloadit(['foo' => 'bar']);
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

    $options = ['foo' => 'bar'];

    $transloadit
      ->method('request')
      ->with($this->equalTo($options + [
        'method'   => 'POST',
        'path'     => '/assemblies',
      ]))
      ->willReturn($assembly);

    $this->assertEquals($assembly, $transloadit->createAssembly($options));
  }

  public function testCancelAssembly() {
    $transloadit = $this->getMockBuilder(Transloadit::class)
                        ->setMethods(['request'])
                        ->getMock();
    $assembly = $this->getMockBuilder(TransloaditResponse::class)
                     ->getMock();
    $response = $this->getMockBuilder(TransloaditResponse::class)
                      ->getMock();

    $assemblyId = 'b7716f21ba1a400f8b1a60a6e1c6acf1';
    $assembly->data = ['assembly_url' => sprintf('https://api2-phpsdktest.transloadit.com/assemblies/%s', $assemblyId)];

    $transloadit
      ->method('request')
      ->withConsecutive(
        [$this->equalTo([
          'method'   => 'GET',
          'path'     => sprintf('/assemblies/%s', $assemblyId),
        ])],
        [$this->equalTo([
          'method'   => 'DELETE',
          'path'     => sprintf('/assemblies/%s', $assemblyId),
          'host'     => 'api2-phpsdktest.transloadit.com',
        ])],
      )
      ->willReturnOnConsecutiveCalls($assembly, $response);

    $this->assertEquals($response, $transloadit->cancelAssembly($assemblyId));
  }

  public function testRequest() {
    $this->transloadit->key = 'my-key';
    $this->transloadit->secret = 'my-secret';
    $request = $this->transloadit->request(['foo' => 'bar'], false);

    $this->assertEquals($this->transloadit->key, $request->key);
    $this->assertEquals($this->transloadit->secret, $request->secret);
    $this->assertEquals('bar', $request->foo);

    // Unfortunately we can't test the $execute parameter because PHP
    // is a little annoying. But that's ok for now.
  }

  public function testResponse() {
    $response = Transloadit::response();
    $this->assertEquals(false, $response);

    $data = ['foo' => 'bar'];
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
    $assembly->fields = [
      'foo' => 'bar"bar',
      'hey' => 'you',
    ];
    $options = ['foo' => 'bar'];

    $transloadit
      ->method('request')
      ->with($this->equalTo($options + [
        'method' => 'POST',
        'path' => '/assemblies',
      ]), $this->equalTo(false))
      ->willReturn($assembly);

    $assembly
      ->method('prepare');

    $options['attributes'] = ['class' => 'nice'];
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
