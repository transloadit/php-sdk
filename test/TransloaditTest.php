<?php
require_once(dirname(dirname(__FILE__)).'/lib/Transloadit.php');

class TransloaditTest extends PHPUnit_Framework_TestCase
{
  public function testAttributes()
  {
    $this->assertClassHasAttribute('key', 'Transloadit');
    $this->assertClassHasAttribute('secret', 'Transloadit');
  }

  public function testConstructor()
  {
    $transloadit = new Transloadit(array('key' => 'foo', 'secret' => 'bar'));
    $this->assertEquals('foo', $transloadit->key);
    $this->assertEquals('bar', $transloadit->secret);
  }

  public function testSign()
  {
    $expected = 'fec703ccbe36b942c90d17f64b71268ed4f5f512';
    $secret = 'd805593620e689465d7da6b8caf2ac7384fdb7e9';

    // Verify the test vector given in the documentation, see: http://transloadit.com/docs/authentication
    $params = '{"auth":{"expires":"2010\/10\/19 09:01:20+00:00","key":"2b0c45611f6440dfb64611e872ec3211"},"steps":{"encode":{"robot":"\/video\/encode"}}}';
    $signature = Transloadit::sign($params, $secret);
    $this->assertEquals($expected, $signature);

    // PHP's json_encode is a little different, so lets verify an example for that as well
    $expected = 'a5b71ecbf4791a2ff69957bd9c044e29ea4d1c18';
    $params = array(
      'auth' => array(
        'expires' => '2010/10/19 09:01:20+00:00',
        'key' => '2b0c45611f6440dfb64611e872ec3211'
       ),
       'steps' => array(
          'robot' => '/video/encode'
       )
    );

    $signature = Transloadit::sign($params, $secret);
    $this->assertEquals($expected, $signature);
  }
}
?>
