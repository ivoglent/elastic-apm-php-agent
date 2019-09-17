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
        $timer->expects(self::atLeastOnce())->method('getDuration')->willReturn(2.0);

        $trace = new TimedTrace();

        $this->setProxyValue($trace, 'timer', $timer);


        $trace->start();
        $trace->stop();

        $this->assertSame(2.0, $trace->getDuration());

        $this->assertSame([], $trace->jsonSerialize());

        $class = new \ReflectionClass($trace);
        $method = $class->getMethod('getTimer');
        $method->setAccessible(true);
        $this->assertInstanceOf(Timer::class, $method->invoke($trace));
    }

    public function testTimestampAndDuration() {
        $timer = $this->createMock(Timer::class);
        $timer->expects(self::once())->method('getNow')->willReturn(1);
        $timer->expects(self::atLeastOnce())->method('getDuration')->willReturn(2.0);

        $trace = new TimedTrace();
        $this->setProxyValue($trace, 'timer', $timer);
        $this->setProxyValue($trace, 'timestamp', 1);

        $trace->start();

        $this->assertSame(2.0, $trace->getDuration());

        $this->assertSame([], $trace->jsonSerialize());

        $class = new \ReflectionClass($trace);
        $method = $class->getMethod('getTimer');
        $method->setAccessible(true);
        $this->assertInstanceOf(Timer::class, $method->invoke($trace));
        $this->assertEquals(1, $trace->getTimestamp());

    }

    private function setProxyValue($object, $name, $value) {
        $reflection = new \ReflectionClass($object);
        $reflectionProperty = $reflection->getProperty($name);
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($object, $value);
        return $object;
    }
}