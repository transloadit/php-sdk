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

    if (system('which tsx > /dev/null 2>&1') === false) {
      throw new \RuntimeException('tsx not found. Please install it with: npm install -g tsx');
    }

    $scriptPath = __DIR__ . '/../../tool/node-smartcdn-sig.ts';
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

  private function assertParityWithNode(string $url, array $params, string $message = ''): void {
    $expectedUrl = $this->getExpectedUrl($params);
    if ($expectedUrl !== null) {
      $this->assertEquals($expectedUrl, $url, $message ?: 'URL should match Node.js reference implementation');
    }
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
      ['expireAtMs' => $params['expire_at_ms']]
    );
    $this->assertMatchesRegularExpression(
      '#^https://workspace\.tlcdn\.com/template/file\.jpg\?auth_key=test-key&exp=\d+&sig=sha256(?:%3A|:)[a-f0-9]+$#',
      $url
    );
    $this->assertParityWithNode($url, $params);

    // Test with input field
    $params['input'] = 'input.jpg';
    $url = $transloadit->signedSmartCDNUrl(
      $params['workspace'],
      $params['template'],
      $params['input'],
      [],
      ['expireAtMs' => $params['expire_at_ms']]
    );
    $this->assertMatchesRegularExpression(
      '#^https://workspace\.tlcdn\.com/template/input\.jpg\?auth_key=test-key&exp=\d+&sig=sha256(?:%3A|:)[a-f0-9]+$#',
      $url
    );
    $this->assertParityWithNode($url, $params);

    // Test with additional params
    $params['input'] = 'file.jpg';
    $params['url_params'] = ['width' => 100];
    $url = $transloadit->signedSmartCDNUrl(
      $params['workspace'],
      $params['template'],
      $params['input'],
      $params['url_params'],
      ['expireAtMs' => $params['expire_at_ms']]
    );
    $this->assertMatchesRegularExpression(
      '#^https://workspace\.tlcdn\.com/template/file\.jpg\?auth_key=test-key&exp=\d+&width=100&sig=sha256(?:%3A|:)[a-f0-9]+$#',
      $url
    );
    $this->assertParityWithNode($url, $params);

    // Test with empty param string
    $params['url_params'] = ['width' => '', 'height' => '200'];
    $url = $transloadit->signedSmartCDNUrl(
      $params['workspace'],
      $params['template'],
      $params['input'],
      $params['url_params'],
      ['expireAtMs' => $params['expire_at_ms']]
    );
    $this->assertMatchesRegularExpression(
      '#^https://workspace\.tlcdn\.com/template/file\.jpg\?auth_key=test-key&exp=\d+&height=200&width=&sig=sha256(?:%3A|:)[a-f0-9]+$#',
      $url
    );
    $this->assertParityWithNode($url, $params);

    // Test with null width parameter (should be excluded)
    $params['url_params'] = ['width' => null, 'height' => '200'];
    $url = $transloadit->signedSmartCDNUrl(
      $params['workspace'],
      $params['template'],
      $params['input'],
      $params['url_params'],
      ['expireAtMs' => $params['expire_at_ms']]
    );
    $this->assertMatchesRegularExpression(
      '#^https://workspace\.tlcdn\.com/template/file\.jpg\?auth_key=test-key&exp=\d+&height=200&sig=sha256(?:%3A|:)[a-f0-9]+$#',
      $url
    );
    $this->assertParityWithNode($url, $params);

    // Test with only empty width parameter
    $params['url_params'] = ['width' => ''];
    $url = $transloadit->signedSmartCDNUrl(
      $params['workspace'],
      $params['template'],
      $params['input'],
      $params['url_params'],
      ['expireAtMs' => $params['expire_at_ms']]
    );
    $this->assertMatchesRegularExpression(
      '#^https://workspace\.tlcdn\.com/template/file\.jpg\?auth_key=test-key&exp=\d+&width=&sig=sha256(?:%3A|:)[a-f0-9]+$#',
      $url
    );
    $this->assertParityWithNode($url, $params);

    // Test with custom sign props
    $params['auth_key'] = 'custom-key';
    $params['auth_secret'] = 'custom-secret';
    $params['expire_at_ms'] = 60000;
    $params['url_params'] = [];
    $url = $transloadit->signedSmartCDNUrl(
      $params['workspace'],
      $params['template'],
      $params['input'],
      $params['url_params'],
      [
        'authKey' => $params['auth_key'],
        'authSecret' => $params['auth_secret'],
        'expireAtMs' => $params['expire_at_ms']
      ]
    );
    $this->assertMatchesRegularExpression(
      '#^https://workspace\.tlcdn\.com/template/file\.jpg\?auth_key=custom-key&exp=60000&sig=sha256(?:%3A|:)[a-f0-9]+$#',
      $url
    );
    $this->assertParityWithNode($url, $params);
  }
}
