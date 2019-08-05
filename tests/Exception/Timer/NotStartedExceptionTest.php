<?php


namespace PhilKra\Tests\Exception\Timer;


use PhilKra\Exception\Timer\NotStartedException;
use PHPUnit\Framework\TestCase;

class NotStartedExceptionTest extends TestCase
{
    public function testCreateException() {
        $exception = new NotStartedException('Test');
        $this->assertEquals('Can\'t stop a timer which isn\'t started.', $exception->getMessage());
    }
}