<?php

namespace transloadit\test\system\Transloadit;

use transloadit\Transloadit;

class TransloaditAssemblyCreateTest extends \PHPUnit\Framework\TestCase {
  public function setUp(): void {
    if (!defined('TRANSLOADIT_KEY') || !defined('TRANSLOADIT_SECRET')) {
      $this->markTestSkipped(
        'Have a look at test/config.php.template to get this test to run.'
      );
      return;
    }

    // @todo Load config from git excluded config file
    $this->transloadit = new Transloadit(['key' => TRANSLOADIT_KEY,
      'secret' => TRANSLOADIT_SECRET,
    ]);
  }
  public function testRoot() {
    $response = $this->transloadit->createAssembly([
      'files' => [TEST_FIXTURE_DIR . '/image-resize-robot.jpg'],
      'params' => [
        'steps' => [
          'resize' => [
            'robot' => '/image/resize',
            'width' => 100,
            'height' => 100,
            'result' => true,
          ],
        ],
      ]
    ]);
    $this->assertEquals('ASSEMBLY_EXECUTING', $response->data['ok']);
  }
}
