<?php

namespace transloadit\Test;

use PHPUnit\Framework\TestCase;
use transloadit\Transloadit;

class SmartCDNNodeParityTest extends TestCase {
  private Transloadit $transloadit;
  private string $workspace;
  private string $template;
  private string $input;
  private int $expireAt;

  protected function setUp(): void {
    if (getenv('TEST_NODE_PARITY') !== '1') {
      $this->markTestSkipped('Node parity tests are disabled. Set TEST_NODE_PARITY=1 to enable.');
    }

    if (system('which tsx > /dev/null 2>&1') === false) {
      $this->markTestSkipped('tsx not available');
    }

    $key = getenv('TRANSLOADIT_KEY') ?: 'my-key';
    $secret = getenv('TRANSLOADIT_SECRET') ?: 'my-secret';

    $this->transloadit = new Transloadit([
      'key' => $key,
      'secret' => $secret
    ]);

    $this->workspace = 'my-app';
    $this->template = 'test-smart-cdn';
    $this->input = 'inputs/prinsengracht.jpg';
    $this->expireAt = 1732550672867;
  }

  private function runNodeScript(array $params): string {
    $scriptPath = __DIR__ . '/../tool/node-smartcdn-sig.ts';
    $jsonInput = json_encode($params);

    $descriptorspec = [
      0 => ["pipe", "r"],  // stdin
      1 => ["pipe", "w"],  // stdout
      2 => ["pipe", "w"]   // stderr
    ];

    $process = proc_open("tsx $scriptPath", $descriptorspec, $pipes);

    if (!is_resource($process)) {
      throw new \RuntimeException('Failed to start Node script');
    }

    fwrite($pipes[0], $jsonInput);
    fclose($pipes[0]);

    $output = stream_get_contents($pipes[1]);
    $error = stream_get_contents($pipes[2]);

    fclose($pipes[1]);
    fclose($pipes[2]);

    $exitCode = proc_close($process);

    if ($exitCode !== 0) {
      throw new \RuntimeException("Node script failed: $error");
    }

    return trim($output);
  }

  public function testBasicUrlGeneration(): void {
    $params = [
      'workspace' => $this->workspace,
      'template' => $this->template,
      'input' => $this->input,
      'expire_at_ms' => $this->expireAt
    ];

    $url = $this->transloadit->signedSmartCDNUrl(
      $params['workspace'],
      $params['template'],
      $params['input'],
      [],
      ['expireAtMs' => $params['expire_at_ms']]
    );
    $nodeUrl = $this->runNodeScript($params);

    $this->assertEquals($nodeUrl, $url, 'Generated URLs should match node implementation');
  }

  public function testUrlParametersHandling(): void {
    $params = [
      'workspace' => $this->workspace,
      'template' => $this->template,
      'input' => $this->input,
      'expire_at_ms' => $this->expireAt,
      'url_params' => [
        'width' => 100,
        'height' => 200
      ]
    ];

    $url = $this->transloadit->signedSmartCDNUrl(
      $params['workspace'],
      $params['template'],
      $params['input'],
      $params['url_params'],
      ['expireAtMs' => $params['expire_at_ms']]
    );
    $nodeUrl = $this->runNodeScript($params);

    $this->assertEquals($nodeUrl, $url, 'URL parameters should be handled the same as node');
  }

  public function testNullValuesInParameters(): void {
    $params = [
      'workspace' => $this->workspace,
      'template' => $this->template,
      'input' => $this->input,
      'expire_at_ms' => $this->expireAt,
      'url_params' => [
        'width' => null,
        'height' => 200
      ]
    ];

    $url = $this->transloadit->signedSmartCDNUrl(
      $params['workspace'],
      $params['template'],
      $params['input'],
      $params['url_params'],
      ['expireAtMs' => $params['expire_at_ms']]
    );
    $nodeUrl = $this->runNodeScript($params);

    $this->assertEquals($nodeUrl, $url, 'Null values in parameters should be handled the same as node');
  }

  public function testEmptyStringValuesInParameters(): void {
    $params = [
      'workspace' => $this->workspace,
      'template' => $this->template,
      'input' => $this->input,
      'expire_at_ms' => $this->expireAt,
      'url_params' => [
        'width' => '',
        'height' => 200
      ]
    ];

    $url = $this->transloadit->signedSmartCDNUrl(
      $params['workspace'],
      $params['template'],
      $params['input'],
      $params['url_params'],
      ['expireAtMs' => $params['expire_at_ms']]
    );
    $nodeUrl = $this->runNodeScript($params);

    $this->assertEquals($nodeUrl, $url, 'Empty string values in parameters should be handled the same as node');
  }

  public function testArrayValuesInParameters(): void {
    $params = [
      'workspace' => $this->workspace,
      'template' => $this->template,
      'input' => $this->input,
      'expire_at_ms' => $this->expireAt,
      'url_params' => [
        'tags' => ['landscape', 'amsterdam', null, ''],
        'height' => 200
      ]
    ];

    $url = $this->transloadit->signedSmartCDNUrl(
      $params['workspace'],
      $params['template'],
      $params['input'],
      $params['url_params'],
      ['expireAtMs' => $params['expire_at_ms']]
    );
    $nodeUrl = $this->runNodeScript($params);

    $this->assertEquals($nodeUrl, $url, 'Array values in parameters should be handled the same as node');
  }

  public function testSpecialCharactersInPaths(): void {
    $params = [
      'workspace' => 'my workspace',
      'template' => 'template/with/slashes',
      'input' => 'input with spaces.jpg',
      'expire_at_ms' => $this->expireAt
    ];

    $url = $this->transloadit->signedSmartCDNUrl(
      $params['workspace'],
      $params['template'],
      $params['input'],
      [],
      ['expireAtMs' => $params['expire_at_ms']]
    );
    $nodeUrl = $this->runNodeScript($params);

    $this->assertEquals($nodeUrl, $url, 'URLs with spaces and special characters should match node implementation');
  }

  public function testEmptyInputField(): void {
    $params = [
      'workspace' => 'workspace',
      'template' => 'template',
      'input' => 'minimal.jpg',
      'expire_at_ms' => $this->expireAt
    ];

    $url = $this->transloadit->signedSmartCDNUrl(
      $params['workspace'],
      $params['template'],
      $params['input'],
      [],
      ['expireAtMs' => $params['expire_at_ms']]
    );
    $nodeUrl = $this->runNodeScript($params);

    $this->assertEquals($nodeUrl, $url, 'Minimal input field should be handled the same as node');
  }
}
