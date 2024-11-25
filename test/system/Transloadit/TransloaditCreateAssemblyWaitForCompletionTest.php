<?php

namespace transloadit\test\system\Transloadit;

use transloadit\Transloadit;

class TransloaditCreateAssemblyWaitForCompletionTest extends \PHPUnit\Framework\TestCase {
  private Transloadit $transloadit;

  public function setUp(): void {
    if (!defined('TRANSLOADIT_KEY') || !defined('TRANSLOADIT_SECRET')) {
      $this->markTestSkipped(
        'Have a look at test/config.php to get this test to run.'
      );
      return;
    }

    // @todo Load config from git excluded config file
    $this->transloadit = new Transloadit([
      'key' => TRANSLOADIT_KEY,
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
      ],
      'waitForCompletion' => true
    ]);
    $this->assertEquals('ASSEMBLY_COMPLETED', $response->data['ok']);

    $getResp = $this->transloadit->getAssembly($response->data['assembly_id']);
    $this->assertEquals('ASSEMBLY_COMPLETED', $getResp->data['ok']);
  }
}
