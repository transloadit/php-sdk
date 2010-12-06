<?php
require_once(dirname(__FILE__).'/TransloaditRequestTestCase.php');

class TransloaditRequestAssemblyCreateTest extends TransloaditRequestTestCase{
  public function testRoot() {
    $this->request->setMethodAndPath('POST', '/assemblies');
    $this->request->files[] = TEST_FIXTURE_DIR.'/image-resize-robot.jpg';
    $this->request->params = array(
      'steps' => array(
        'resize' => array(
          'robot' => '/image/resize',
          'width' => 100,
          'height' => 100,
          'result' => true,
        ),
      ),
    );
    $response = $this->request->execute();

    $this->assertEquals('ASSEMBLY_EXECUTING', $response->data['ok']);
  }
}
