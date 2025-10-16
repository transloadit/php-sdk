<?php

namespace transloadit\test\simple;

use transloadit\Transloadit;
use transloadit\TransloaditResponse;

date_default_timezone_set('UTC');

class TransloaditTest extends \PHPUnit\Framework\TestCase {
  protected $transloadit;
  public function setUp(): void {
    $this->transloadit = new Transloadit();
  }

  public function testConstructor() {
    $transloadit = new Transloadit(['endpoint' => 'foobar']);
    $this->assertEquals('foobar', $transloadit->endpoint);
  }

  public function testAttributes() {
    $this->assertEquals($this->transloadit->key, null);
    $this->assertEquals($this->transloadit->secret, null);
  }

  public function testCreateAssembly() {
    $transloadit = $this->getMockBuilder(Transloadit::class)
      ->setMethods(['request'])
      ->getMock();
    $assembly = $this->getMockBuilder(TransloaditResponse::class)
      ->getMock();

    $options = ['foo' => 'bar'];

    $transloadit
      ->method('request')
      ->with($this->equalTo($options + [
        'method'   => 'POST',
        'path'     => '/assemblies',
      ]))
      ->willReturn($assembly);

    $this->assertEquals($assembly, $transloadit->createAssembly($options));
  }

  public function testCancelAssembly() {
    $transloadit = $this->getMockBuilder(Transloadit::class)
      ->setMethods(['request'])
      ->getMock();
    $assembly = $this->getMockBuilder(TransloaditResponse::class)
      ->getMock();
    $response = $this->getMockBuilder(TransloaditResponse::class)
      ->getMock();

    $assemblyId = 'b7716f21ba1a400f8b1a60a6e1c6acf1';
    $assembly->data = ['assembly_url' => sprintf('https://api2-phpsdktest.transloadit.com/assemblies/%s', $assemblyId)];

    $transloadit
      ->method('request')
      ->withConsecutive(
        [$this->equalTo([
          'method'   => 'GET',
          'path'     => sprintf('/assemblies/%s', $assemblyId),
        ])
        ],
        [$this->equalTo([
          'method'   => 'DELETE',
          'path'     => sprintf('/assemblies/%s', $assemblyId),
          'host'     => 'api2-phpsdktest.transloadit.com',
        ])
        ],
      )
      ->willReturnOnConsecutiveCalls($assembly, $response);

    $this->assertEquals($response, $transloadit->cancelAssembly($assemblyId));
  }

  public function testRequest() {
    $this->transloadit->key = 'my-key';
    $this->transloadit->secret = 'my-secret';
    $request = $this->transloadit->request(['url' => 'foobar'], false);

    $this->assertEquals($this->transloadit->key, $request->key);
    $this->assertEquals($this->transloadit->secret, $request->secret);
    $this->assertEquals('foobar', $request->url);

    // Unfortunately we can't test the $execute parameter because PHP
    // is a little annoying. But that's ok for now.
  }

  public function testResponse() {
    $response = Transloadit::response();
    $this->assertEquals(false, $response);

    $data = ['foo' => 'bar'];
    $_POST['transloadit'] = json_encode($data);
    $response = Transloadit::response();
    $this->assertInstanceOf(TransloaditResponse::class, $response);
    $this->assertEquals($data, $response->data);


    // Can't really test the $_GET['assembly_url'] case because of PHP for now.
  }

  public function testCreateAssemblyForm() {
    $transloadit = $this->getMockBuilder(Transloadit::class)
      ->setMethods(['request'])
      ->getMock();
    $assembly = $this->getMockBuilder(TransloaditResponse::class)
      ->setMethods(['prepare'])
      ->getMock();

    $assembly->method = 'ROCK';
    $assembly->url = 'http://api999.transloadit.com/assemblies';
    $assembly->fields = [
      'foo' => 'bar"bar',
      'hey' => 'you',
    ];
    $options = ['foo' => 'bar'];

    $transloadit
      ->method('request')
      ->with($this->equalTo($options + [
        'method' => 'POST',
        'path' => '/assemblies',
      ]), $this->equalTo(false))
      ->willReturn($assembly);

    $assembly
      ->method('prepare');

    $options['attributes'] = ['class' => 'nice'];
    $tags = explode("\n", $transloadit->createAssemblyForm($options));

    $formTag = array_shift($tags);
    $this->assertTrue(preg_match('/action="http:\/\/api999\.transloadit\.com\/assemblies"/', $formTag) !== false);
    $this->assertTrue(preg_match('/method="ROCK"/', $formTag) !== false);
    $this->assertTrue(preg_match('/enctype="multipart\/form-data"/', $formTag) !== false);
    $this->assertTrue(preg_match('/class="nice"/', $formTag) !== false);

    foreach ($assembly->fields as $field => $val) {
      $inputTag = array_shift($tags);
      $this->assertTrue(preg_match('/type="hidden"/', $inputTag) !== false);
      $this->assertTrue(preg_match('/name="' . $field . '"/', $inputTag) !== false);
      $this->assertTrue(preg_match('/value="' . $val . '"/', $inputTag) !== false);
    }
  }

  private function getExpectedUrl(array $params): ?string {
    if (getenv('TEST_NODE_PARITY') !== '1') {
      return null;
    }

    exec('command -v npm 2>/dev/null', $output, $returnVar);
    if ($returnVar !== 0) {
      throw new \RuntimeException('npm command not found. Please install Node.js (which includes npm).');
    }

    if (!isset($params['auth_key']) || !isset($params['auth_secret'])) {
      throw new \InvalidArgumentException('auth_key and auth_secret are required for parity testing');
    }

    try {
      $cliParams = [
        'workspace' => $params['workspace'],
        'template' => $params['template'],
        'input' => $params['input'],
      ];
      if (array_key_exists('url_params', $params)) {
        $cliParams['url_params'] = $params['url_params'];
      }
      if (array_key_exists('expire_at_ms', $params)) {
        $cliParams['expire_at_ms'] = $params['expire_at_ms'];
      }
      $jsonInput = json_encode($cliParams, JSON_THROW_ON_ERROR);
    } catch (\JsonException $e) {
      throw new \RuntimeException('Failed to encode parameters for Node parity test: ' . $e->getMessage(), 0, $e);
    }

    $command = 'npm exec --yes --package transloadit@4.0.5 -- transloadit smart_sig';

    $descriptorspec = [
      0 => ["pipe", "r"],  // stdin
      1 => ["pipe", "w"],  // stdout
      2 => ["pipe", "w"],  // stderr
    ];

    $originalKey = getenv('TRANSLOADIT_KEY');
    $originalSecret = getenv('TRANSLOADIT_SECRET');
    $originalAuthKey = getenv('TRANSLOADIT_AUTH_KEY');
    $originalAuthSecret = getenv('TRANSLOADIT_AUTH_SECRET');

    putenv('TRANSLOADIT_KEY=' . $params['auth_key']);
    putenv('TRANSLOADIT_SECRET=' . $params['auth_secret']);
    putenv('TRANSLOADIT_AUTH_KEY=' . $params['auth_key']);
    putenv('TRANSLOADIT_AUTH_SECRET=' . $params['auth_secret']);

    try {
      $process = proc_open($command, $descriptorspec, $pipes);

      if (!is_resource($process)) {
        throw new \RuntimeException('Failed to start transloadit CLI parity command');
      }

      fwrite($pipes[0], $jsonInput);
      fclose($pipes[0]);

      $stdout = stream_get_contents($pipes[1]);
      $stderr = stream_get_contents($pipes[2]);

      fclose($pipes[1]);
      fclose($pipes[2]);

      $exitCode = proc_close($process);

      if ($exitCode !== 0) {
        $message = trim($stderr) !== '' ? trim($stderr) : 'Command exited with status ' . $exitCode;
        throw new \RuntimeException('transloadit CLI parity command failed: ' . $message);
      }

      return trim($stdout);
    } finally {
      if ($originalKey !== false) {
        putenv('TRANSLOADIT_KEY=' . $originalKey);
      } else {
        putenv('TRANSLOADIT_KEY');
      }

      if ($originalSecret !== false) {
        putenv('TRANSLOADIT_SECRET=' . $originalSecret);
      } else {
        putenv('TRANSLOADIT_SECRET');
      }

      if ($originalAuthKey !== false) {
        putenv('TRANSLOADIT_AUTH_KEY=' . $originalAuthKey);
      } else {
        putenv('TRANSLOADIT_AUTH_KEY');
      }

      if ($originalAuthSecret !== false) {
        putenv('TRANSLOADIT_AUTH_SECRET=' . $originalAuthSecret);
      } else {
        putenv('TRANSLOADIT_AUTH_SECRET');
      }
    }
  }

  private function assertParityWithNode(string $url, array $params, string $message = ''): void {
    $expectedUrl = $this->getExpectedUrl($params);
    if ($expectedUrl !== null) {
      $this->assertEquals($expectedUrl, $url, $message ?: 'URL should match Node.js reference implementation');
    }
  }

  private function debugUrls(string $phpUrl, string $nodeUrl, string $message = ''): void {
    echo "\n\nDebug $message:\n";
    echo "PHP URL:  $phpUrl\n";
    echo "Node URL: $nodeUrl\n\n";
  }

  public function testSignedSmartCDNUrl() {
    $transloadit = new Transloadit([
      'key' => 'test-key',
      'secret' => 'test-secret'
    ]);

    // Use fixed timestamp for all tests
    $expireAtMs = 1732550672867;

    // Test basic URL generation
    $params = [
      'workspace' => 'workspace',
      'template' => 'template',
      'input' => 'file.jpg',
      'auth_key' => 'test-key',
      'auth_secret' => 'test-secret',
      'expire_at_ms' => $expireAtMs
    ];
    $url = $transloadit->signedSmartCDNUrl(
      $params['workspace'],
      $params['template'],
      $params['input'],
      [],
      $expireAtMs
    );
    $nodeUrl = $this->getExpectedUrl($params);
    $expectedUrl = 'https://workspace.tlcdn.com/template/file.jpg?auth_key=test-key&exp=1732550672867&sig=sha256%3Ad994b8a737db1c43d6e04a07018dc33e8e28b23b27854bd6383d828a212cfffb';
    $this->assertEquals($expectedUrl, $url, 'PHP URL should match expected');
    $this->assertEquals($expectedUrl, $nodeUrl, 'Node.js URL should match expected');

    // Test with input field
    $params['input'] = 'input.jpg';
    $url = $transloadit->signedSmartCDNUrl(
      $params['workspace'],
      $params['template'],
      $params['input'],
      [],
      $expireAtMs
    );
    $nodeUrl = $this->getExpectedUrl($params);
    $expectedUrl = 'https://workspace.tlcdn.com/template/input.jpg?auth_key=test-key&exp=1732550672867&sig=sha256%3A75991f02828d194792c9c99f8fea65761bcc4c62dbb287a84f642033128297c0';
    $this->assertEquals($expectedUrl, $url, 'PHP URL should match expected');
    $this->assertEquals($expectedUrl, $nodeUrl, 'Node.js URL should match expected');

    // Test with additional params
    $params['input'] = 'file.jpg';
    $params['url_params'] = ['width' => 100];
    $url = $transloadit->signedSmartCDNUrl(
      $params['workspace'],
      $params['template'],
      $params['input'],
      $params['url_params'],
      $expireAtMs
    );
    $nodeUrl = $this->getExpectedUrl($params);
    $expectedUrl = 'https://workspace.tlcdn.com/template/file.jpg?auth_key=test-key&exp=1732550672867&width=100&sig=sha256%3Ae5271d8fb6482d9351ebe4285b6fc75539c4d311ff125c4d76d690ad71c258ef';
    $this->assertEquals($expectedUrl, $url, 'PHP URL should match expected');
    $this->assertEquals($expectedUrl, $nodeUrl, 'Node.js URL should match expected');

    // Test with empty param string
    $params['url_params'] = ['width' => '', 'height' => '200'];
    $url = $transloadit->signedSmartCDNUrl(
      $params['workspace'],
      $params['template'],
      $params['input'],
      $params['url_params'],
      $expireAtMs
    );
    $nodeUrl = $this->getExpectedUrl($params);
    $expectedUrl = 'https://workspace.tlcdn.com/template/file.jpg?auth_key=test-key&exp=1732550672867&height=200&width=&sig=sha256%3A1a26733c859f070bc3d83eb3174650d7a0155642e44a5ac448a43bc728bc0f85';
    $this->assertEquals($expectedUrl, $url, 'PHP URL should match expected');
    $this->assertEquals($expectedUrl, $nodeUrl, 'Node.js URL should match expected');

    // Test with null width parameter (should be excluded)
    $params['url_params'] = ['width' => null, 'height' => '200'];
    $url = $transloadit->signedSmartCDNUrl(
      $params['workspace'],
      $params['template'],
      $params['input'],
      $params['url_params'],
      $expireAtMs
    );
    $nodeUrl = $this->getExpectedUrl($params);
    $expectedUrl = 'https://workspace.tlcdn.com/template/file.jpg?auth_key=test-key&exp=1732550672867&height=200&sig=sha256%3Adb740ebdfad6e766ebf6516ed5ff6543174709f8916a254f8d069c1701cef517';
    $this->assertEquals($expectedUrl, $url, 'PHP URL should match expected');
    $this->assertEquals($expectedUrl, $nodeUrl, 'Node.js URL should match expected');

    // Test with only empty width parameter
    $params['url_params'] = ['width' => ''];
    $url = $transloadit->signedSmartCDNUrl(
      $params['workspace'],
      $params['template'],
      $params['input'],
      $params['url_params'],
      $expireAtMs
    );
    $nodeUrl = $this->getExpectedUrl($params);
    $expectedUrl = 'https://workspace.tlcdn.com/template/file.jpg?auth_key=test-key&exp=1732550672867&width=&sig=sha256%3A840426f9ac72dde02fd080f09b2304d659fdd41e630b1036927ec1336c312e9d';
    $this->assertEquals($expectedUrl, $url, 'PHP URL should match expected');
    $this->assertEquals($expectedUrl, $nodeUrl, 'Node.js URL should match expected');
  }

  public function testTransloaditCliRequiredForParityTesting(): void {
    if (getenv('TEST_NODE_PARITY') !== '1') {
      $this->markTestSkipped('Parity testing not enabled');
    }

    // Temporarily override PATH to simulate missing npm
    $originalPath = getenv('PATH');
    putenv('PATH=');

    try {
      $params = [
        'workspace' => 'test',
        'template' => 'test',
        'input' => 'test.jpg',
        'auth_key' => 'test',
        'auth_secret' => 'test'
      ];
      $this->getExpectedUrl($params);
      $this->fail('Expected RuntimeException when npm is not available');
    } catch (\RuntimeException $e) {
      $this->assertStringContainsString('npm command not found', $e->getMessage());
    } finally {
      // Restore original PATH
      putenv("PATH=$originalPath");
    }
  }

  public function testExpireAtMs(): void {
    $transloadit = new Transloadit([
      'key' => 'test-key',
      'secret' => 'test-secret'
    ]);

    // Test with explicit expireAtMs
    $expireAtMs = 1732550672867;
    $params = [
      'workspace' => 'workspace',
      'template' => 'template',
      'input' => 'file.jpg',
      'auth_key' => 'test-key',
      'auth_secret' => 'test-secret',
      'expire_at_ms' => $expireAtMs
    ];

    $url = $transloadit->signedSmartCDNUrl(
      $params['workspace'],
      $params['template'],
      $params['input'],
      [],
      $params['expire_at_ms']
    );

    $this->assertStringContainsString("exp=$expireAtMs", $url);
    $this->assertParityWithNode($url, $params);

    // Test default expiry (should be about 1 hour from now)
    unset($params['expire_at_ms']);
    $url = $transloadit->signedSmartCDNUrl(
      $params['workspace'],
      $params['template'],
      $params['input']
    );

    $matches = [];
    preg_match('/exp=(\d+)/', $url, $matches);
    $this->assertNotEmpty($matches[1], 'URL should contain expiry timestamp');

    $expiry = (int)$matches[1];
    $now = time() * 1000;
    $oneHour = 60 * 60 * 1000;

    $this->assertGreaterThan($now, $expiry, 'Expiry should be in the future');
    $this->assertLessThan($now + $oneHour + 5000, $expiry, 'Expiry should be about 1 hour from now');
    $this->assertGreaterThan($now + $oneHour - 5000, $expiry, 'Expiry should be about 1 hour from now');

    // For parity test, set the exact expiry time to match Node.js
    $params['expire_at_ms'] = $expiry;
    $this->assertParityWithNode($url, $params);
  }
}
