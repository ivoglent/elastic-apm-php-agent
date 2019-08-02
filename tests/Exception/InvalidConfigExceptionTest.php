<?php


namespace PhilKra\Tests\Exception\Timer;


use PhilKra\Exception\InvalidConfigException;
use PHPUnit\Framework\TestCase;

class InvalidConfigExceptionTest extends TestCase
{
    public function testCreateException() {
        $exception = new InvalidConfigException('Test');
        $this->assertEquals('No app name registered in agent config.', $exception->getMessage());
    }
}