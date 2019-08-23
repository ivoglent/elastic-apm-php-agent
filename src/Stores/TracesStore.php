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

use PhilKra\Traces\Error;
use PhilKra\Traces\Span;
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
     * @var array of PhilKra\Traces\Trace
     */
    protected $store = [];

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
     * Generator to ND-JSON for Intake API v2
     * if object is not instance of Span or Error which contains child array type
     *
     *
     * @return string
     */
    public function toNdJson(): string
    {
        return sprintf("%s\n", implode("\n", array_map(function ($obj) {
            if (($obj instanceof Span || $obj instanceof Error) && !empty($obj->getStacktrace())) {
                return json_encode($obj);
            }

            return json_encode($obj, JSON_FORCE_OBJECT);
        }, $this->list())));
    }
}
