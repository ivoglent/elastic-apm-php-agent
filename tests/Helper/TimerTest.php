<?php

namespace PhilKra\Tests\Helper;

use PhilKra\Exception\Timer\AlreadyRunningException;
use PhilKra\Exception\Timer\NotStartedException;
use PhilKra\Exception\Timer\NotStoppedException;
use \PhilKra\Helper\Timer;
use PhilKra\Tests\TestCase;

/**
 * Test Case for @see \PhilKra\Helper\Timer
 */
final class TimerTest extends TestCase
{

    /**
     * @covers \PhilKra\Helper\Timer::start
     * @covers \PhilKra\Helper\Timer::stop
     * @covers \PhilKra\Helper\Timer::getDuration
     * @covers \PhilKra\Helper\Timer::milliSeconds()
     */
    public function testCanBeStartedAndStoppedWithDuration()
    {
        $timer = new Timer();
        $duration = rand(25, 100);

        $timer->start();
        usleep($duration * 1000);
        $timer->stop();

        $this->assertGreaterThanOrEqual($duration, $timer->getDuration());
    }


    public function testCanCalculateDurationInMilliseconds()
    {
        $timer = new Timer();
        $now = microtime(true);

        $this->assertGreaterThanOrEqual($now, $timer->getNow());
    }

    /**
     * @depends testCanBeStartedAndStoppedWithDuration
     *
     */
    public function testGetElapsedDurationWithoutError()
    {
        $timer = new Timer();

        $timer->start();
        usleep(10);
        $elapsed = $timer->getElapsed();
        $timer->stop();

        $this->assertGreaterThanOrEqual($elapsed, $timer->getDuration());
        $this->assertEquals($timer->getElapsed(), $timer->getDuration());
    }


    /**
     *
     * @covers  \PhilKra\Helper\Timer::stop
     */
    public function testCannotBeStoppedWithoutStart()
    {
        $timer = new Timer();

        $this->expectException(NotStartedException::class);

        $timer->stop();
    }

    /**
     *
     * @covers  \PhilKra\Helper\Timer::getDuration
     */
    public function testGetDurationWithoutStart()
    {
        $timer = new Timer();

        $this->expectException(NotStartedException::class);

        $timer->getDuration();
    }

    /**
     *
     * @covers  \PhilKra\Helper\Timer::getDuration
     */
    public function testGetDurationWithoutStop()
    {
        $timer = new Timer();
        $timer->start();
        sleep(1);
        $this->assertGreaterThan(1, $timer->getDuration());
    }

    public function testCannotGetElapseWithoutStart()
    {
        $timer = new Timer();

        $this->expectException(NotStartedException::class);

        $timer->getElapsed();
    }

    /**
     * @covers \PhilKra\Helper\Timer::start
     * @covers \PhilKra\Helper\Timer::getDuration()
     */
    public function testCanBeStartedWithExplicitStartTime()
    {
        $timer = new Timer(microtime(true) - .5); // Start timer 500 milliseconds ago

        usleep(500 * 1000); // Sleep for 500 milliseconds

        $timer->stop();

        $duration = $timer->getDuration();

        // Duration should be more than 1000 milliseconds
        //  sum of initial offset and sleep
        $this->assertGreaterThanOrEqual(1000, $duration);
    }

    /**
     * @covers \PhilKra\Helper\Timer::start
     */
    public function testCannotBeStartedIfAlreadyRunning()
    {
        $timer = new Timer(microtime(true));

        $this->expectException(AlreadyRunningException::class);
        $timer->start();
    }
}
