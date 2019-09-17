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

namespace PhilKra\Factories;

use PhilKra\Helper\Config;
use PhilKra\Traces\Error;
use PhilKra\Traces\Metadata;
use PhilKra\Traces\Metricset;
use PhilKra\Traces\Span;
use PhilKra\Traces\Transaction;

final class DefaultTracesFactory implements TracesFactory
{

    /**
     * @var \PhilKra\Helper\Config
     */
    private $config;

    /**
     * @param \PhilKra\Helper\Config $config
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    /**
     * {@inheritdoc}
     */
    public function newError(\Throwable $throwable): Error
    {
        return new Error($throwable);
    }

    /**
     * {@inheritdoc}
     */
    public function newSpan(string $name, string $type, ?string $subtype = null, ?string $action = null): Span
    {
        return new Span($name, $type, $subtype, $action);
    }

    /**
     * {@inheritdoc}
     */
    public function newTransaction(string $name, string $type): Transaction
    {
        return new Transaction($name, $type);
    }

    /**
     * {@inheritdoc}
     */
    public function newMetricset(): Metricset
    {
        return new Metricset();
    }

    /**
     * {@inheritdoc}
     */
    public function newMetadata(): Metadata
    {
        return new Metadata($this->config);
    }
}
