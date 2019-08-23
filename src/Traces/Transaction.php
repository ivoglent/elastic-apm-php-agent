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

/**
 * APM Transaction
 *
 * @see https://www.elastic.co/guide/en/apm/server/6.7/transaction-api.html
 * @version 6.7 (v2)
 *
 * @required ["duration", "type"]
 */
class Transaction extends Event
{
    /**
     * Keyword of specific relevance in the service's domain (eg: 'request', 'backgroundjob', etc)
     *
     * @var string
     */
    private $type;

    /**
     * Generic designation of a transaction in the scope of a single service (eg: 'GET /users/:id')
     *
     * @var string
     */
    private $name;

    /**
     * The result of the transaction. For HTTP-related transactions, this should be the status code formatted like 'HTTP 2xx'.
     *
     * @var string
     */
    private $result;

    /**
     * @var Context | null
     */
    private $context;

    /**
     * Transactions that are 'sampled' will include all available information. Transactions that are not sampled will not have 'spans' or 'context'. Defaults to true.
     *
     * @var bool
     */
    private $sampled = true;

    /**
     * @var int
     */
    private $droppedSpan = 0;

    /**
     * @var integer
     */
    private $startedSpan = 0;

    public function __construct(string $name, string $type)
    {
        parent::__construct();

        $this->name = trim($name);
        $this->type = trim($type);
    }

    public function stop(string $result = null): void
    {
        $this->result = $result;
        parent::stop();
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType(string $type): void
    {
        $this->type = $type;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @param string|null $result
     */
    public function setResult(?string $result = null): void
    {
        $this->result = $result;
    }

    /**
     * @param Context|null $context
     */
    public function setContext(?Context $context = null): void
    {
        $this->context = $context;
    }

    /**
     * @return bool
     */
    public function getSampled(): bool
    {
        return $this->sampled;
    }

    /**
     * @param bool $sampled
     */
    public function setSampled(bool $sampled): void
    {
        $this->sampled = $sampled;
    }

    /**
     * @param int $droppedSpan
     */
    public function setDroppedSpan(int $droppedSpan): void
    {
        $this->droppedSpan = $droppedSpan;
    }

    /**
     * @param int $startedSpan
     */
    public function setStartedSpan(int $startedSpan): void
    {
        $this->startedSpan = $startedSpan;
    }



    /**
     * Serialize Error
     *
     * @return array
     */
    public function jsonSerialize(): array
    {
        $payload = [
          'transaction' => [
              'id' => $this->getId(),
              'trace_id' => $this->getTraceId(),
              'result' => $this->result,
              'name' => $this->name,
              'type' => $this->type,
              'timestamp' => $this->timestamp,
              'duration' => $this->duration,
              'sampled' => $this->sampled,
              'span_count' => [
                  'started' => $this->startedSpan,
                  'dropped' => $this->droppedSpan,
              ],
              'context' => $this->context
          ],
        ];

        return $payload;
    }
}
