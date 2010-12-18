<?php
require_once(dirname(dirname(__FILE__)).'/BaseTestCase.php');
require_once(TEST_LIB_DIR.'/Transloadit.php');

class TransloaditTest extends BaseTestCase{
  public function setUp() {
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
    $transloadit = $this->getMock('Transloadit', array(
      'request'
    ));

    $assembly = $this->getMock('TransloaditResponse');
    $boredInstance = $this->getMock('TransloaditResponse');
    $boredInstance->data = array('api2_host' => 'super.transloadit.com');

    $options = array('foo' => 'bar');

    $transloadit
      ->expects($this->at(0))
      ->method('request')
      ->with($this->equalTo(array(
        'method' => 'GET',
        'path' => '/instances/bored',
      )))
      ->will($this->returnValue($boredInstance));

    $boredInstance
      ->expects($this->at(0))
      ->method('error')
      ->will($this->returnValue(false));

    $transloadit
      ->expects($this->at(1))
      ->method('request')
      ->with($this->equalTo($options + array(
        'method' => 'POST',
        'path' => '/assemblies',
        'host' => $boredInstance->data['api2_host'],
      )))
      ->will($this->returnValue($assembly));

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
    $this->assertEquals('TransloaditResponse', get_class($response));
    $this->assertEquals($data, $response->data);


    // Can't really test the $_GET['assembly_url'] case because of PHP for now.
  }

  public function testCreateAssemblyForm() {
    $transloadit = $this->getMock('Transloadit', array('request'));
    $assembly = $this->getMock('TransloaditResponse', array('prepare'));

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
      ->will($this->returnValue($assembly));

    $assembly
      ->expects($this->at(0))
      ->method('prepare');

    $options['attributes'] = array('class' => 'nice');
    $tags = explode("\n", $transloadit->createAssemblyForm($options));

    $this->assertTag(array(
      'tag' => 'form',
      'attributes' => array(
        'action' => $assembly->url,
        'method' => $assembly->method,
        'enctype' => 'multipart/form-data',
        'class' => 'nice',
      )
    ), array_shift($tags));

    foreach ($assembly->fields as $field => $val) {
      $matcher = array(
        'tag' => 'input',
        'attributes' => array(
          'type' => 'hidden',
          'name' => $field,
          'value' => $val,
        )
      );
      $tag = array_shift($tags);
      $this->assertTag($matcher, $tag, sprintf(
        'Tag %s does not match %s',
        $tag,
        json_encode($matcher)
      ));
    }
  }
}
?>
