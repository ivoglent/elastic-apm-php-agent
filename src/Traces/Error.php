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
 * APM Error
 *
 * @see https://www.elastic.co/guide/en/apm/server/6.7/error-api.html
 * @version 6.7 (v2)
 */
class Error extends Event
{

    /**
     * @var array
     */
    private $stacktrace;

    /**
     * @var
     */
    private $context;

    /**
     * Error | Exception
     *
     * @see http://php.net/manual/en/class.throwable.php
     *
     * @var \Throwable
     */
    private $throwable;

    /**
     * @param \Throwable $throwable
     * @param array $contexts
     */
    public function __construct(\Throwable $throwable, array $contexts = null)
    {
        parent::__construct();
        $this->throwable = $throwable;
        $this->stacktrace = self::mapStacktrace($this->throwable->getTrace());
        $this->context = $contexts;
    }

    /**
     * @return mixed
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * @return string
     */
    public function getTransactionId()
    {
        return $this->transaction_id;
    }

    /**
     * @param string $tranaction_id
     */
    public function setTransactionId(string  $tranaction_id)
    {
        $this->transaction_id = $tranaction_id;
    }

    /**
     * Return current transaction
     *
     * @return Transaction
     */
    public function getTransaction()
    {
        return $this->transaction;
    }

    /**
     * Get debug stack of traces
     *
     * @return array
     */
    public function getStacktrace()
    {
        return $this->stacktrace;
    }

    /**
     * Serialize Error Event
     *
     * @return array
     */
    public function jsonSerialize(): array
    {
        $payload = [
            'error' => [
                'id' => $this->getId(),
                'timestamp' => $this->timestamp,
                'context' => $this->getContext(),
                'culprit' => sprintf('%s:%d', $this->throwable->getFile(), $this->throwable->getLine()),
                'exception' => [
                    'message' => $this->throwable->getMessage(),
                    'type' => get_class($this->throwable),
                    'code' => $this->throwable->getCode(),
                    'stacktrace' => self::mapStacktrace($this->throwable->getTrace()),
                ],
                'parent_id' => $this->getParentId(),
                'trace_id' => $this->getTraceId(),
                'transaction_id' => $this->getTransactionId(),
                'transaction' => $this->transaction,
            ],
        ];

        return $payload;
    }

    /**
     * Map the Stacktrace to Schema
     *
     * @return array
     */
    public static function mapStacktrace(array $traces): array
    {
        $stacktrace = [];

        foreach ($traces as $trace) {
            $item = [
                'function' => $trace['function'] ?? '(closure)',
            ];

            if (true === isset($trace['line'])) {
                $item['lineno'] = $trace['line'];
            }

            if (true === isset($trace['file'])) {
                $item['filename'] = basename($trace['file']);
                $item['abs_path'] = ($trace['file']);
            }

            if (true === isset($trace['class'])) {
                $item['module'] = $trace['class'];
            }

            if (!isset($item['lineno'])) {
                $item['lineno'] = 0;
            }

            if (!isset($item['filename'])) {
                $item['filename'] = '(anonymous)';
            }

            $stacktrace[] = $item;
        }

        return $stacktrace;
    }
}
