<?php


namespace PhilKra\Tests\Exception\Timer;


use PhilKra\Exception\Timer\NotStoppedException;
use PHPUnit\Framework\TestCase;

class NotStoppedExceptionTest extends TestCase
{
    public function testCreateException() {
        $exception = new NotStoppedException('Test');
        $this->assertEquals('Can\'t get the duration of a running timer.', $exception->getMessage());
    }
}