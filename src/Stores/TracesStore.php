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

namespace PhilKra\Stores;

use PhilKra\Exception\InvalidTimeException;
use PhilKra\Exception\Timer\NotStartedException;
use PhilKra\Traces\Error;
use PhilKra\Traces\Span;
use PhilKra\Traces\TimedTrace;
use PhilKra\Traces\Trace;
use PhilKra\Traces\Transaction;

/**
 * Registry for <b>all</b> captured Events
 */
class TracesStore implements \JsonSerializable
{
    /**
     * Set of Traces
     *
     * @var Trace[] array of PhilKra\Traces\Trace
     */
    protected $store = [];

    /**
     * @var Transaction
     */
    private $transaction;

    /**
     * Get all Registered Errors
     *
     * @return array of PhilKra\Traces\Trace
     */
    public function list(): array
    {
        return $this->store;
    }

    /**
     * Register a Trace
     *
     * @param Trace $t
     * @internal param $Trace
     */
    public function register(Trace $t): void
    {
        if ($t instanceof Transaction) {
            $this->transaction = $t;
        }
        $this->store[] = $t;
    }

    /**
     * Is the Store Empty ?
     *
     * @return bool
     */
    public function isEmpty(): bool
    {
        return empty($this->store);
    }

    /**
     * Empty the Store
     */
    public function reset()
    {
        $this->store = [];
    }

    /**
     * Serialize the Events Store
     *
     * @return array
     */
    public function jsonSerialize(): array
    {
        return $this->store;
    }

    /**
     * @throws InvalidTimeException
     * @throws NotStartedException
     * @throws \PhilKra\Exception\Timer\NotStoppedException
     */
    private function validate() {
        //Validate timestamp
        $maxTimeRange = $this->transaction->getTimestamp() + $this->transaction->getDuration() * 1000;
        $totalDuration = 0;
        foreach ($this->store as $trace) {
            if ($trace instanceof Span) {
                if ($trace->getTimestamp() < $this->transaction->getTimestamp()) {
                    throw new InvalidTimeException('Span timestamp can not be less than transaction timestamp');
                }
                if ($trace->getDuration() > $this->transaction->getDuration()) {
                    throw new InvalidTimeException('Span duration can not be greater than transaction duration');
                }
                $totalDuration += $trace->getDuration();
                if ($trace->getTimestamp() + $trace->getDuration() * 1000 > $maxTimeRange) {
                    throw new InvalidTimeException('Invalid end of span time position');
                }
            }
        }
        if ($this->transaction->getDuration() < $totalDuration) {
            throw new InvalidTimeException('Transaction duration can not be less than total span duration');
        }

    }

    /**
     * Generator to ND-JSON for Intake API v2
     * if object is not instance of Span or Error which contains child array type
     *
     *
     * @return string
     * @throws InvalidTimeException
     * @throws NotStartedException
     * @throws \PhilKra\Exception\Timer\NotStoppedException
     */
    public function toNdJson(): string
    {
        if ($this->isEmpty() === false && $this->transaction) {
            $this->validate();
        }
        return sprintf("%s\n", implode("\n", array_map(function ($obj) {
            if (($obj instanceof Span || $obj instanceof Error) && !empty($obj->getStacktrace())) {
                return json_encode($obj);
            }

            return json_encode($obj, JSON_FORCE_OBJECT);
        }, $this->list())));
    }
}
