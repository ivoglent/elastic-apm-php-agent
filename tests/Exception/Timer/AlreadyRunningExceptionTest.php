<?php


namespace PhilKra\Tests\Exception\Timer;


use PhilKra\Exception\Timer\AlreadyRunningException;
use PHPUnit\Framework\TestCase;

class AlreadyRunningExceptionTest extends TestCase
{
    public function testCreateException() {
        $exception = new AlreadyRunningException('Test');
        $this->assertEquals('Can\'t start a timer which is already running.', $exception->getMessage());
    }
}