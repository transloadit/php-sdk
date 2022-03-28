<?php
use transloadit\Transloadit;

class TransloaditCreateAssemblyWaitForCompletionTest extends \PHPUnit\Framework\TestCase{
  private Transloadit $transloadit;

  public function setUp(): void {
    if (!defined('TEST_ACCOUNT_KEY') || !defined('TEST_ACCOUNT_SECRET')) {
      $this->markTestSkipped(
        'Have a look at test/config.php.template to get this test to run.'
      );
      return;
    }

    // @todo Load config from git excluded config file
    $this->transloadit = new Transloadit([
      'key' => TEST_ACCOUNT_KEY,
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
      ],
      'waitForCompletion' => true
    ]);
    $this->assertEquals('ASSEMBLY_COMPLETED', $response->data['ok']);

    $getResp = $this->transloadit->getAssembly($response->data['assembly_id']);
    $this->assertEquals('ASSEMBLY_COMPLETED', $getResp->data['ok']);
  }
}
