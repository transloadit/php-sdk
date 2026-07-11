<?php

namespace transloadit\test\simple;

use transloadit\Transloadit;

class TestableBearerTokenClient extends Transloadit {
  public function bearerTokenRequest($options) {
    return $this->createBearerTokenRequest($options);
  }
}

class TransloaditBearerTokenTest extends \PHPUnit\Framework\TestCase {
  public function testBuildsBasicAuthFormRequestWithoutAudienceOverride() {
    $client = new TestableBearerTokenClient([
      'endpoint' => 'http://127.0.0.1:9040',
      'key' => 'key',
      'secret' => 'secret',
    ]);

    $request = $client->bearerTokenRequest(['scope' => 'assemblies:read']);

    $this->assertSame('POST', $request->method);
    $this->assertSame('http://127.0.0.1:9040/token', $request->url);
    $this->assertSame([
      'grant_type' => 'client_credentials',
      'scope' => 'assemblies:read',
    ], $request->fields);
    $this->assertArrayNotHasKey('aud', $request->fields);
    $this->assertContains('Authorization: Basic a2V5OnNlY3JldA==', $request->headers);
    $this->assertFalse($request->getCurlOptions()[CURLOPT_FOLLOWLOCATION]);
  }
}
