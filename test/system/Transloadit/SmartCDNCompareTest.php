<?php

namespace transloadit\test\system\Transloadit;

use transloadit\Transloadit;

class SmartCDNCompareTest extends \PHPUnit\Framework\TestCase {
  private $transloadit;

  public function setUp(): void {
    if (!defined('TEST_ACCOUNT_KEY') || !defined('TEST_ACCOUNT_SECRET')) {
      $this->markTestSkipped(
        'Have a look at test/config.php.template to get this test to run.'
      );
      return;
    }

    $this->transloadit = new Transloadit([
      'key' => TEST_ACCOUNT_KEY,
      'secret' => TEST_ACCOUNT_SECRET,
    ]);
  }

  private function getNodeSignedUrl($expiryMs, $workspace, $template, $input) {
    $cmd = sprintf(
      'TRANSLOADIT_KEY=%s TRANSLOADIT_SECRET=%s tsx tool/smartcdn-sig.ts %d %s %s %s',
      escapeshellarg(TEST_ACCOUNT_KEY),
      escapeshellarg(TEST_ACCOUNT_SECRET),
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
      [
        'authKey' => TEST_ACCOUNT_KEY,
        'authSecret' => TEST_ACCOUNT_SECRET,
        'expiresAtMs' => $expiryMs,
      ]
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