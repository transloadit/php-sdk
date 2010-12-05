<?php
require_once(dirname(dirname(__FILE__)).'/TransloaditTestCase.php');
require_once(TEST_LIB_DIR.'/TransloaditRequest.php');

class TransloaditRequestTest extends TransloaditTestCase{
  public function setUp() {
    $this->request = new TransloaditRequest();
  }

  private function _mock() {
    $methods = func_get_args();
    $this->request = $this->getMock('TransloaditRequest', $methods);
  }

  public function testConstructor() {
    $parent = get_parent_class($this->request);
    $this->assertEquals('CurlRequest', $parent);
  }

  public function testAttributes() {
    $this->assertEquals($this->request->service, 'http://api2.transloadit.com');
    $this->assertEquals($this->request->key, null);
    $this->assertEquals($this->request->secret, null);
    $this->assertEquals($this->request->params, array());
    $this->assertEquals($this->request->prepareature, null);
    $this->assertEquals($this->request->expires, '+2 hours');
    $this->assertEquals('Expect:', $this->request->headers[0]);
    $this->assertEquals('User-Agent: Transloadit PHP SDK 0.1', $this->request->headers[1]);
  }

  public function testInit() {
    $METHOD = 'CONNECT';
    $PATH = '/foo';

    $this->request->init($METHOD, $PATH);
    $this->assertEquals($METHOD, $this->request->method);
    $this->assertEquals($this->request->service.$PATH, $this->request->url);
  }

  public function testPrepare() {
    $this->_mock('getParamsString', 'setField', 'signString');

    $this->request->secret = 'dsakjsdsadjkl241132423';
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
      ->method('setField')
      ->with($this->equalTo('params'), $this->equalTo($PARAMS_STRING));

    $this->request
      ->expects($this->at(3))
      ->method('setField')
      ->with($this->equalTo('signature'), $this->equalTo($SIGNATURE_STRING));

    $this->request->prepare();
  }

  public function testSignString() {
    $this->request->secret = 'd805593620e689465d7da6b8caf2ac7384fdb7e9';
    $expectedSignature = 'fec703ccbe36b942c90d17f64b71268ed4f5f512';

    // Verify the test vector given in the documentation, see: http://transloadit.com/docs/authentication
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
