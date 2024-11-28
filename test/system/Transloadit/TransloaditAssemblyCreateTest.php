<?php

namespace transloadit\test\system\Transloadit;

use transloadit\Transloadit;

class TransloaditAssemblyCreateTest extends \PHPUnit\Framework\TestCase {
  private Transloadit $transloadit;

  public function setUp(): void {
    if (!getenv('TRANSLOADIT_KEY') || !getenv('TRANSLOADIT_SECRET')) {
      $this->markTestSkipped(
        'TRANSLOADIT_KEY and TRANSLOADIT_SECRET environment variables are required.'
      );
      return;
    }

    $this->transloadit = new Transloadit([
      'key' => getenv('TRANSLOADIT_KEY'),
      'secret' => getenv('TRANSLOADIT_SECRET'),
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
