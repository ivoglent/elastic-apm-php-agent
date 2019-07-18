<?php
/**
 * This file is part of the PhilKra/elastic-apm-php-agent library
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license http://opensource.org/licenses/MIT MIT
 * @link https://github.com/philkra/elastic-apm-php-agent GitHub
 */

namespace PhilKra\Traces;

use PhilKra\Traces\SpanContexts\SpanContext;

/**
 * APM Error
 *
 * @link https://www.elastic.co/guide/en/apm/server/6.7/span-api.html
 * @version 6.7 (v2)
 *
 * "required": ["duration", "name", "type"]
 * "required": ["id", "transaction_id", "trace_id", "parent_id"]
 */
class Span extends Event
{

    /**
     * Duration of the span in milliseconds
     *
     * @var int
     */
    private $duration;

    /**
     * Generic designation of a span in the scope of a transaction
     *
     * @var string
     */
    private $name;

    /**
     * List of stack frames with variable attributes (eg: lineno, filename, etc)
     *
     * @ref "../stacktrace_frame.json"
     *
     * @var mixed array | null
     */
    private $stacktrace;

    /**
     * Keyword of specific relevance in the service's domain (eg: 'db.postgresql.query', 'template.erb', etc)
     *
     * @var string
     */
    private $type;


    /**
     * Indicates whether the span was executed synchronously or asynchronously.
     *
     * @var bool
     */
    private $sync = false;

    /**
     * Offset relative to the transaction's timestamp identifying the start of the span, in milliseconds
     *
     * @var int
     */
    private $start;

    /**
     * A further sub-division of the type (e.g. postgresql, elasticsearch)
     *
     * @var string
     */
    private $subtype;

    /**
     * Span Contexts
     *
     * @var array
     */
    private $contexts = [];

    /**
     * The specific kind of event within the sub-type represented by the span (e.g. query, connect)
     *
     * @var string
     */
    private $action = null;

    public function __construct(string $name, string $type, string $action = null)
    {
        parent::__construct();

        $this->name = trim($name);
        $this->type = trim($type);
        $this->action = $action;
    }

    /**
     * @param float|null $initAt
     * @throws \PhilKra\Exception\Timer\NotStartedException
     */
    public function start(?float $initAt = null): void
    {
        parent::start($initAt);
        $this->start = $this->transaction->getTimer()->getElapsed();
    }

    /**
     * @param string $action
     */
    public function setAction(string $action): void
    {
        $this->action = $action;
    }


    /**
     * Add a SpanContext
     *
     * @param SpanContext $context
     */
    public function addContext($type = 'db', SpanContext $context) : void
    {
        $this->contexts[$type] = $context;
    }

    /**
     * Set stack trace for this span
     *
     * @param Stacktrace $stacktrace
     */
    public function addStacktrace(Stacktrace $stacktrace) {
        $this->stacktrace[] = $stacktrace;
    }

    /**
     * Set stack traces for this span
     *
     * @param Stacktrace[] $stacktrace
     */
    public function addStacktraces(array $stacktraces) {
        $this->stacktrace = $stacktraces;
    }

    /**
     * Get current stack straces
     *
     * @return mixed
     */
    public function getStacktrace() {
        return $this->stacktrace;
    }
    
    /**
     * Serialize Span
     *
     * @return array
     */
    public function jsonSerialize() : array
    {
        $payload = [
          'span' => [
              'id'             => $this->getId(),
              'action'         => $this->action,
              'transaction_id' => $this->transaction_id,
              'trace_id'       => $this->getTraceId(),
              'start'          => $this->start,
              'parent_id'      => $this->getParentId(),
              'name'           => $this->name,
              'type'           => $this->type,
              'timestamp'      => $this->timestamp,
              'duration'       => $this->getDuration(),
              'sync'           => $this->sync,
              'context'        => empty($this->contexts) ? null : $this->contexts,
              'stacktrace'     => $this->stacktrace
          ]
      ];

      return $payload;
    }

}
