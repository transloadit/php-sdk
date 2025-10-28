<?php

namespace transloadit\test\simple;

use transloadit\TransloaditRequest;
use transloadit\CurlRequest;

class TransloaditRequestTest extends \PHPUnit\Framework\TestCase {
  private $request;

  public function setUp(): void {
    $this->request = new TransloaditRequest();
  }

  public function testConstructor() {
    $this->assertInstanceOf('transloadit\\CurlRequest', $this->request);
  }

  public function testAttributes() {
    $this->assertEquals($this->request->endpoint, 'https://api2.transloadit.com');
    $this->assertEquals($this->request->path, null);
    $this->assertEquals($this->request->key, null);
    $this->assertEquals($this->request->secret, null);
    $this->assertEquals($this->request->params, []);
    $this->assertEquals($this->request->expires, '+2 hours');
    $this->assertEquals('Expect:', $this->request->headers[0]);
    $this->assertContains('Transloadit-Client: php-sdk:%s', $this->request->headers);
  }

  public function testInit() {
    $METHOD = 'CONNECT';
    $PATH = '/foo';

    $this->request->setMethodAndPath($METHOD, $PATH);
    $this->assertEquals($METHOD, $this->request->method);
    $this->assertEquals($PATH, $this->request->path);
  }

  public function testPrepare() {
    $this->request = $this->getMockBuilder(TransloaditRequest::class)
      ->setMethods(['getParamsString', 'signString', 'configureUrl'])
      ->getMock();

    $PARAMS_STRING = '{super}';
    $SIGNATURE_STRING = 'dsasjhdsajda';

    $this->request
      ->method('getParamsString')
      ->willReturn($PARAMS_STRING);

    $this->request
      ->method('signString')
      ->with($this->equalTo($PARAMS_STRING))
      ->willReturn($SIGNATURE_STRING);

    $this->request
      ->method('configureUrl');

    $this->request->prepare();
    $this->assertEquals($PARAMS_STRING, $this->request->fields['params']);
    $this->assertEquals($SIGNATURE_STRING, $this->request->fields['signature']);

    // Without signature
    $this->request = $this->getMockBuilder(TransloaditRequest::class)
      ->setMethods(['getParamsString', 'signString', 'configureUrl'])
      ->getMock();
    $SIGNATURE_STRING = null;

    $this->request
      ->method('getParamsString')
      ->will($this->returnValue($PARAMS_STRING));

    $this->request
      ->method('signString')
      ->with($this->equalTo($PARAMS_STRING))
      ->will($this->returnValue($SIGNATURE_STRING));

    $this->request
      ->method('configureUrl');

    $this->request->prepare();
    $this->assertEquals($PARAMS_STRING, $this->request->fields['params']);
    $this->assertArrayNotHasKey('signature', $this->request->fields);
  }

  public function testConfigureUrl() {
    $PATH     = $this->request->path = '/foo';
    $ENDPOINT = $this->request->endpoint = 'ftp://bar.com';
    $this->request->configureUrl();

    $this->assertEquals('ftp://bar.com/foo', $this->request->url);

    $URL = $this->request->url = 'http://custom.org/manual';
    $this->request->configureUrl();
    $this->assertEquals($URL, $this->request->url);
  }

  public function testSignString() {
    // No secret, no signature
    $this->assertEquals(null, $this->request->signString('foo'));

    // Verify the test vector given in the documentation, see: http://transloadit.com/docs/authentication
    $this->request->secret = 'd805593620e689465d7da6b8caf2ac7384fdb7e9';
    $expectedSignature = 'fec703ccbe36b942c90d17f64b71268ed4f5f512';

    $params = '{"auth":{"expires":"2010\/10\/19 09:01:20+00:00","key":"2b0c45611f6440dfb64611e872ec3211"},"steps":{"encode":{"robot":"\/video\/encode"}}}';
    $signature = $this->request->signString($params);
    $this->assertEquals($expectedSignature, $signature);
  }

  public function testGetParamsString() {
    $this->request->key = 'dskjadjk2j42jkh4';
    $PARAMS = $this->request->params = ['foo' => 'bar'];
    $paramsString = $this->request->getParamsString();
    $params = json_decode($paramsString, true);

    $this->assertEquals($this->request->key, $params['auth']['key']);
    $this->assertEquals(gmdate('Y/m/d H:i:s+00:00', strtotime($this->request->expires)), $params['auth']['expires']);
    $this->assertEquals($PARAMS['foo'], $params['foo']);
  }

  public function testSignatureParityWithNodeCli(): void {
    if (getenv('TEST_NODE_PARITY') !== '1') {
      $this->markTestSkipped('Parity testing not enabled');
    }

    $request = new TransloaditRequest();
    $request->key = 'cli-key';
    $request->secret = 'cli-secret';
    $request->expires = '2025-01-02 00:00:00+00:00';
    $request->params = [
      'auth' => ['expires' => '2025-01-02 00:00:00+00:00'],
      'steps' => [
        'resize' => [
          'robot' => '/image/resize',
          'width' => 320,
        ],
      ],
    ];

    $cliResult = $this->getCliSignature([
      'auth' => ['expires' => '2025-01-02 00:00:00+00:00'],
      'steps' => [
        'resize' => [
          'robot' => '/image/resize',
          'width' => 320,
        ],
      ],
    ], 'cli-key', 'cli-secret', 'sha1');

    $this->assertNotNull($cliResult);
    $this->assertArrayHasKey('signature', $cliResult);
    $this->assertArrayHasKey('params', $cliResult);

    $cliParams = json_decode($cliResult['params'], true);
    $phpParams = json_decode($request->getParamsString(), true);

    $this->assertEquals('cli-key', $cliParams['auth']['key']);
    $this->assertEquals($phpParams['auth']['expires'], $cliParams['auth']['expires']);
    $this->assertEquals(
      $phpParams['steps']['resize']['robot'],
      $cliParams['steps']['resize']['robot']
    );
    $this->assertEquals(
      $phpParams['steps']['resize']['width'],
      $cliParams['steps']['resize']['width']
    );

    $expectedSignature = hash_hmac('sha1', $cliResult['params'], 'cli-secret');
    $this->assertEquals('sha1:' . $expectedSignature, $cliResult['signature']);
  }

  public function testExecute() {
    // Can't test this method because PHP doesn't allow stubbing the calls
    // to curl easily. However, the method hardly contains any logic as all
    // of that is located in other methods.
    $this->assertTrue(true);
  }

  private function getCliSignature(array $params, string $key, string $secret, ?string $algorithm = null): ?array {
    if (getenv('TEST_NODE_PARITY') !== '1') {
      return null;
    }

    exec('command -v npm 2>/dev/null', $output, $returnVar);
    if ($returnVar !== 0) {
      throw new \RuntimeException('npm command not found. Please install Node.js (which includes npm).');
    }

    try {
      $jsonInput = json_encode($params, JSON_THROW_ON_ERROR);
    } catch (\JsonException $e) {
      throw new \RuntimeException('Failed to encode parameters for Node parity test: ' . $e->getMessage(), 0, $e);
    }

    $command = 'npm exec --yes --package transloadit@4.0.5 -- transloadit sig';
    if ($algorithm !== null) {
      $command .= ' --algorithm ' . escapeshellarg($algorithm);
    }

    $descriptorspec = [
      0 => ["pipe", "r"],  // stdin
      1 => ["pipe", "w"],  // stdout
      2 => ["pipe", "w"],  // stderr
    ];

    $originalKey = getenv('TRANSLOADIT_KEY');
    $originalSecret = getenv('TRANSLOADIT_SECRET');
    $originalAuthKey = getenv('TRANSLOADIT_AUTH_KEY');
    $originalAuthSecret = getenv('TRANSLOADIT_AUTH_SECRET');

    putenv('TRANSLOADIT_KEY=' . $key);
    putenv('TRANSLOADIT_SECRET=' . $secret);
    putenv('TRANSLOADIT_AUTH_KEY=' . $key);
    putenv('TRANSLOADIT_AUTH_SECRET=' . $secret);

    try {
      $process = proc_open($command, $descriptorspec, $pipes);

      if (!is_resource($process)) {
        throw new \RuntimeException('Failed to start transloadit CLI sig command');
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
        throw new \RuntimeException('transloadit CLI sig command failed: ' . $message);
      }

      return json_decode(trim($stdout), true, 512, JSON_THROW_ON_ERROR);
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
}
