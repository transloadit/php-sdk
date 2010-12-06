<?php
require_once(dirname(__FILE__).'/TransloaditTestCase.php');

class TransloaditAssemblyCreateTest extends TransloaditTestCase{
  public function testRoot() {
    $response = $this->transloadit->createAssembly(array(
      'files' => array(TEST_FIXTURE_DIR.'/image-resize-robot.jpg'),
      'params' => array(
        'steps' => array(
          'resize' => array(
            'robot' => '/image/resize',
            'width' => 100,
            'height' => 100,
            'result' => true,
          ),
        ),
      )
    ));
    $this->assertEquals('ASSEMBLY_EXECUTING', $response->data['ok']);
  }
}
