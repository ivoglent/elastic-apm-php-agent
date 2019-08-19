<?php


namespace PhilKra\Tests\Traces;


use PhilKra\Traces\Context;
use PHPUnit\Framework\TestCase;

class ContextTest extends TestCase
{
    public function testSetRequest() {
        $context = new Context();
        $context->setRequest('http://localhost', 'GET');
        $this->assertSame('{"request":{"method":"GET","url":{"raw":"http:\/\/localhost","protocol":"http","full":"http:\/\/localhost","hostname":"","port":80,"pathname":"","search":"","hash":""}}}',json_encode($context->jsonSerialize()));
    }
}