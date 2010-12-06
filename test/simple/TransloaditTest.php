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
    // I wish I could test this method, but PHP does not love me.
  }
}
?>
