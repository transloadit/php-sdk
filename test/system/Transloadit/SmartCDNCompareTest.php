<?php

namespace transloadit\test\system\Transloadit;

use transloadit\Transloadit;

class SmartCDNCompareTest extends \PHPUnit\Framework\TestCase {
  private $transloadit;

  public function setUp(): void {
    if (!getenv('TEST_NODE_PARITY')) {
      $this->markTestSkipped(
        'Node comparison test is opt-in. Set TEST_NODE_PARITY=1 to run.'
      );
      return;
    }

    if (!getenv('TRANSLOADIT_KEY') || !getenv('TRANSLOADIT_SECRET')) {
      $this->markTestSkipped(
        'TRANSLOADIT_KEY and TRANSLOADIT_SECRET environment variables are required.'
      );
      return;
    }

    // Check if tsx is available
    exec('which tsx', $output, $returnCode);
    if ($returnCode !== 0) {
      $this->markTestSkipped(
        'tsx not found. Install with: npm install -g tsx'
      );
      return;
    }

    $this->transloadit = new Transloadit([
      'key' => getenv('TRANSLOADIT_KEY'),
      'secret' => getenv('TRANSLOADIT_SECRET'),
    ]);
  }

  private function getNodeSignedUrl($expiryMs, $workspace, $template, $input) {
    $cmd = sprintf(
      'TRANSLOADIT_KEY=%s TRANSLOADIT_SECRET=%s tsx tool/node-smartcdn-sig.ts %d %s %s %s',
      escapeshellarg(getenv('TRANSLOADIT_KEY')),
      escapeshellarg(getenv('TRANSLOADIT_SECRET')),
      $expiryMs,
      escapeshellarg($workspace),
      escapeshellarg($template),
      escapeshellarg($input)
    );

    exec($cmd, $output, $returnCode);
    $this->assertEquals(0, $returnCode, "Node script failed with code {$returnCode}");
    return trim($output[0]);
  }

  private function getPhpSignedUrl($expiryMs, $workspace, $template, $input) {
    return $this->transloadit->signedSmartCDNUrl(
      $workspace,
      $template,
      $input,
      [],
      ['expiresAtMs' => $expiryMs]
    );
  }

  public function testUrlSigningMatches() {
    $testCases = [
      [
        'expiryMs' => 1732550672867,
        'workspace' => 'my-app',
        'template' => 'test-smart-cdn',
        'input' => 'inputs/prinsengracht.jpg',
        'description' => 'Basic path with forward slash',
      ],
      [
        'expiryMs' => 1732550672867,
        'workspace' => 'my workspace',
        'template' => 'template/with/slashes',
        'input' => 'input with spaces.jpg',
        'description' => 'Paths with spaces and special characters',
      ],
      [
        'expiryMs' => 1732550672867,
        'workspace' => 'workspace',
        'template' => 'template',
        'input' => '',
        'description' => 'Empty input field',
      ],
    ];

    foreach ($testCases as $test) {
      $nodeUrl = $this->getNodeSignedUrl(
        $test['expiryMs'],
        $test['workspace'],
        $test['template'],
        $test['input']
      );

      $phpUrl = $this->getPhpSignedUrl(
        $test['expiryMs'],
        $test['workspace'],
        $test['template'],
        $test['input']
      );

      $this->assertEquals(
        $nodeUrl,
        $phpUrl,
        "URLs don't match for test case: {$test['description']}\nNode: {$nodeUrl}\nPHP:  {$phpUrl}"
      );
    }
  }
}
