<?php
use transloadit\Transloadit;

class TransloaditAssemblyCreateTest extends \PHPUnit\Framework\TestCase{
  public function setUp(): void {
    if (!defined('TEST_ACCOUNT_KEY') || !defined('TEST_ACCOUNT_SECRET')) {
      $this->markTestSkipped(
        'Have a look at test/config.php.template to get this test to run.'
      );
      return;
    }

    // @todo Load config from git excluded config file
    $this->transloadit = new Transloadit(['key' => TEST_ACCOUNT_KEY,
      'secret' => TEST_ACCOUNT_SECRET,
    ]);
  }
  public function testRoot() {
    $response = $this->transloadit->createAssembly([
      'files' => [TEST_FIXTURE_DIR.'/image-resize-robot.jpg'],
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
