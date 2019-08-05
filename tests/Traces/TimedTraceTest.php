<?php


namespace PhilKra\Tests\Traces;


use PhilKra\Helper\Timer;
use PhilKra\Traces\TimedTrace;
use PHPUnit\Framework\TestCase;

class TimedTraceTest extends TestCase
{
    public function testTimedTrace() {
        $timer = $this->createMock(Timer::class);
        $timer->expects(self::once())->method('getNow')->willReturn(1);
        $timer->expects(self::atLeastOnce())->method('getElapsed')->willReturn(2.0);

        $trace = new TimedTrace();
        $reflection = new \ReflectionClass($trace);
        $reflectionProperty = $reflection->getProperty('timer');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($trace, $timer);


        $trace->start();
        $trace->stop();

        $this->assertSame(2.0, $trace->getDuration());

        $this->assertSame([], $trace->jsonSerialize());

        $class = new \ReflectionClass($trace);
        $method = $class->getMethod('getTimer');
        $method->setAccessible(true);

        $this->assertInstanceOf(Timer::class, $method->invoke($trace));
    }
}