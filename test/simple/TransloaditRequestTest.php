<?php

use transloadit\TransloaditRequest;
use transloadit\CurlRequest;

class TransloaditRequestTest extends \PHPUnit_Framework_TestCase{
  public function setUp() {
    $this->request = new TransloaditRequest();
  }

  private function _mock() {
    $methods = func_get_args();
    $this->request = $this->getMock('transloadit\\TransloaditRequest', $methods);
  }

  public function testConstructor() {
    $this->assertInstanceOf('transloadit\\CurlRequest', $this->request);
  }

  public function testAttributes() {
    $this->assertEquals($this->request->endpoint, 'https://api2.transloadit.com');
    $this->assertEquals($this->request->path, null);
    $this->assertEquals($this->request->key, null);
    $this->assertEquals($this->request->secret, null);
    $this->assertEquals($this->request->params, array());
    $this->assertEquals($this->request->expires, '+2 hours');
    $this->assertEquals('Expect:', $this->request->headers[0]);
    $this->assertContains('Transloadit-Client: php-sdk:%s', $this->request->headers[1]);
  }

  public function testInit() {
    $METHOD = 'CONNECT';
    $PATH = '/foo';

    $this->request->setMethodAndPath($METHOD, $PATH);
    $this->assertEquals($METHOD, $this->request->method);
    $this->assertEquals($PATH, $this->request->path);
  }

  public function testPrepare() {
    // With secret
    $this->_mock(
      'getParamsString',
      'signString',
      'configureUrl'
    );

    $PARAMS_STRING = '{super}';
    $SIGNATURE_STRING = 'dsasjhdsajda';

    $this->request
      ->expects($this->at(0))
      ->method('getParamsString')
      ->will($this->returnValue($PARAMS_STRING));

    $this->request
      ->expects($this->at(1))
      ->method('signString')
      ->with($this->equalTo($PARAMS_STRING))
      ->will($this->returnValue($SIGNATURE_STRING));

    $this->request
      ->expects($this->at(2))
      ->method('configureUrl');

    $this->request->prepare();
    $this->assertEquals($PARAMS_STRING, $this->request->fields['params']);
    $this->assertEquals($SIGNATURE_STRING, $this->request->fields['signature']);

    // Without signature
    $this->_mock(
      'getParamsString',
      'signString',
      'configureUrl'
    );
    $SIGNATURE_STRING = null;

    $this->request
      ->expects($this->at(0))
      ->method('getParamsString')
      ->will($this->returnValue($PARAMS_STRING));

    $this->request
      ->expects($this->at(1))
      ->method('signString')
      ->with($this->equalTo($PARAMS_STRING))
      ->will($this->returnValue($SIGNATURE_STRING));

    $this->request
      ->expects($this->at(2))
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
    $PARAMS = $this->request->params = array('foo' => 'bar');
    $paramsString = $this->request->getParamsString();
    $params = json_decode($paramsString, true);

    $this->assertEquals($this->request->key, $params['auth']['key']);
    $this->assertEquals(gmdate('Y/m/d H:i:s+00:00', strtotime($this->request->expires)), $params['auth']['expires']);
    $this->assertEquals($PARAMS['foo'], $params['foo']);
  }

  public function testExecute() {
    // Can't test this method because PHP doesn't allow stubbing the calls
    // to curl easily. However, the method hardly contains any logic as all
    // of that is located in other methods.
  }
}
?>
