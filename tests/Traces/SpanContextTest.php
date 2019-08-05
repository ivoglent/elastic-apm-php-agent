<?php


namespace PhilKra\Tests\Traces;


use PhilKra\Traces\SpanContexts\Http;
use PHPUnit\Framework\TestCase;

class SpanContextTest extends TestCase
{
    public function testHttp() {
        $http = new Http('url', 200, 'get');
        $this->assertSame([
            'url' => 'url',
            'status_code' => 200,
            'method' => 'get'
        ], $http->jsonSerialize());
    }
}