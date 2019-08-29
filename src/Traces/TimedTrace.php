<?php
/**
 * This file is part of the PhilKra/elastic-apm-php-agent library
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license http://opensource.org/licenses/MIT MIT
 * @see https://github.com/philkra/elastic-apm-php-agent GitHub
 */

namespace PhilKra\Traces;

use PhilKra\Exception\Timer\AlreadyRunningException;
use PhilKra\Exception\Timer\NotStartedException;
use PhilKra\Helper\Timer;

/**
 * Trace with Timing Context
 */
class TimedTrace implements Trace
{

    /** @var float */
    protected $timestamp;
    /**
     * @var Timer
     */
    private $timer;

    /** @var float */
    protected $duration;

    /**
     * Init the Event with the Timestamp
     */
    public function __construct()
    {
        $this->timer = new Timer();
        $this->timestamp = $this->timer->getNow();
    }

    /**
     * Start the Event Time (at microtime X)
     *
     * @param float|null $initAt
     * @throws AlreadyRunningException
     */
    public function start(?float $initAt = null): void
    {
        $this->timer->start($initAt);
        $this->timestamp = $this->timer->getNow();
    }

    /**
     * Stop the Timer
     */
    public function stop(): void
    {
        $this->timer->stop();
        $this->duration = $this->timer->getDuration();
    }

    /**
     * Get the Duration
     *
     * @return float
     * @throws NotStartedException
     * @throws \PhilKra\Exception\Timer\NotStoppedException
     */
    public function getDuration(): float
    {
        if (null === $this->duration) {
            $this->stop();
        }
        return $this->duration;
    }

    /**
     * @return Timer
     */
    protected function getTimer(): Timer
    {
        return $this->timer;
    }

    /**
     * @return float
     */
    public function getTimestamp(): float
    {
        return $this->timestamp;
    }

    /**
     * {@inheritdoc}
     */
    public function jsonSerialize(): array
    {
        return [];
    }
}
