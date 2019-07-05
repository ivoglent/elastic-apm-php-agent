<?php

namespace PhilKra\Helper;

use PhilKra\Exception\Timer\AlreadyRunningException;
use PhilKra\Exception\Timer\NotStartedException;
use PhilKra\Exception\Timer\NotStoppedException;

/**
 * Timer for Duration tracing
 */
class Timer
{
    /**
     * Starting Timestamp
     *
     * @var float
     */
    private $startedOn = null;

    /**
     * Ending Timestamp
     *
     * @var float
     */
    private $stoppedOn = null;

    /**
     * Get the Event's Timestamp Epoch in Micro
     *
     * @return int
     */
    public function getNow() : int
    {
        return time() * 1000000;
    }

    /**
     * Start the Timer
     *
     * @return void
     * @throws AlreadyRunningException
     */
    public function start(float $startTime = null) : void
    {
        if (null !== $this->startedOn) {
            throw new AlreadyRunningException();
        }
        $this->startedOn = ($startTime !== null) ? $startTime : microtime(true);
    }

    /**
     * Stop the Timer
     *
     * @throws \PhilKra\Exception\Timer\NotStartedException
     *
     * @return void
     */
    public function stop() : void
    {
        if ($this->startedOn === null) {
            throw new NotStartedException();
        }

        $this->stoppedOn = microtime(true);
    }

    /**
     * Get the elapsed Duration of this Timer in MicroSeconds
     *
     * @throws \PhilKra\Exception\Timer\NotStoppedException
     *
     * @return float
     */
    public function getDuration() : float
    {
        if ($this->stoppedOn === null) {
            throw new NotStoppedException();
        }
        return $this->microToMili($this->stoppedOn - $this->startedOn);
    }

    /**
     * Get the current elapsed Interval of the Timer in MicroSeconds
     *
     * @throws \PhilKra\Exception\Timer\NotStartedException
     *
     * @return float
     */
    public function getElapsed() : float
    {
        if ($this->startedOn === null) {
            throw new NotStartedException();
        }

        return ($this->stoppedOn === null) ?
            $this->microToMili(microtime(true) - $this->startedOn) :
            $this->getDuration();
    }

    /**
     * @param float $num
     * @return float
     */
    private function microToMili(float $num):float {
        return round($num * 1000, 3);
    }
}
