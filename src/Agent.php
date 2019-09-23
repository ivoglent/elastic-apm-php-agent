<?php

declare(strict_types=1);

/**
 * This file is part of the PhilKra/elastic-apm-php-agent library
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license http://opensource.org/licenses/MIT MIT
 * @see https://github.com/philkra/elastic-apm-php-agent GitHub
 */

namespace PhilKra;

use PhilKra\Factories\DefaultTracesFactory;
use PhilKra\Factories\TracesFactory;
use PhilKra\Helper\Config;
use PhilKra\Helper\MetricHelper;
use PhilKra\Helper\Timer;
use PhilKra\Stores\TracesStore;
use PhilKra\Traces\Metricset;
use PhilKra\Traces\Metricset\Metric;
use PhilKra\Traces\Span;
use PhilKra\Traces\Trace;
use PhilKra\Traces\Transaction;
use PhilKra\Transport\TransportFactory;
use PhilKra\Transport\TransportInterface;

/**
 * APM Agent
 */
class Agent
{
    /**
     * Agent Version
     *
     * @var string
     */
    public const VERSION = '6.6.11';

    /**
     * Agent Name
     *
     * @var string
     */
    public const NAME = 'php';

    /**
     * Config Store
     *
     * @var Config
     */
    private $config;

    /**
     * Traces Store
     *
     * @var \PhilKra\Stores\TracesStore
     */
    public $traces;

    /**
     * Apm Timer
     *
     * @var \PhilKra\Helper\Timer
     */
    private $timer;

    /**
     * Common/Shared Contexts for Errors and Transactions
     *
     * @var array
     */
    private $sharedContext = [
        'user' => [],
        'custom' => [],
        'tags' => [],
    ];

    /**
     * @var DefaultTracesFactory
     */
    private $factory;

    /**
     * @var TransportInterface
     */
    private $httpClient;

    /**
     * @var bool
     */
    private $sampleRateApplied = false;

    /**
     * @var int
     */
    private $droppedSpan = 0;

    /**
     * @var int
     */
    private $startedSpan = 0;

    /**
     * Setup the APM Agent
     *
     * @param array $config
     * @param array $sharedContext Set shared contexts such as user and tags
     * @throws Exception\InvalidConfigException
     * @throws Exception\Timer\AlreadyRunningException
     * @throws Exception\Timer\NotStartedException
     */
    public function __construct(array $config, array $sharedContext = [])
    {
        // Init Agent Config
        $this->config = new Config($config);
        $this->sharedContext = array_merge($this->sharedContext, $sharedContext);
        $this->init();
    }

    private function init() {
        // Init http client
        $this->httpClient = TransportFactory::new($this->config);

        // Init the Traces Factory
        $this->factory = new DefaultTracesFactory($this->getConfig());

        // Init the Traces Store
        $this->traces = new TracesStore();

        // Generate Metadata Trace
        $metadata = $this->factory->newMetadata();
        $metadata->getUser()->initFromArray($this->sharedContext['user']);
        $this->register($metadata);

        // Let's misuse the context to pass the environment variable and cookies
        // config to the EventBeans and the getContext method
        // @see https://github.com/philkra/elastic-apm-php-agent/issues/27
        // @see https://github.com/philkra/elastic-apm-php-agent/issues/30
        $this->sharedContext['env'] = $this->config->get('env', []);
        $this->sharedContext['cookies'] = $this->config->get('cookies', []);

        // Start Global Agent Timer
        $this->timer = new Timer();
        $this->timer->start();

        $this->sampleRateApplied = false;
        $this->droppedSpan = 0;
        $this->startedSpan = 0;

        $txtRate = (float) $this->config->get('sampleRate', 1.0);
        if ($txtRate < 1.0 && mt_rand(1, 100) > ($txtRate * 100)) {
            // @codeCoverageIgnoreStart
            $this->sampleRateApplied = true;
            // @codeCoverageIgnoreEnd
        }
    }
    /**
     * Inject a Custom Traces Factory
     *
     * @param TracesFactory $factory
     */
    public function setFactory(TracesFactory $factory)
    {
        $this->factory = $factory;
    }

    /**
     * Public Interface to generate Traces
     *
     * @return TracesFactory
     */
    public function factory(): TracesFactory
    {
        return $this->factory;
    }

    /**
     * Get the Agent Config
     *
     * @return Config
     */
    public function getConfig(): Config
    {
        return $this->config;
    }

    /**
     * Put a Trace in the Registry
     *
     * @param Trace $trace
     * @throws Exception\Timer\NotStartedException
     * @throws Exception\Timer\NotStoppedException
     */
    public function register(Trace $trace): void
    {
        if ($trace instanceof Span) {
            $this->startedSpan++;
            if ($this->sampleRateApplied) {
                $this->droppedSpan++;
                return;
            }
            if ($trace->getDuration() < $this->config->get('minimumSpanDuration', 20)) {
                $this->droppedSpan++;
                return;
            }
            if (count($this->traces->list()) > $this->config->get('maximumTransactionSpan', 100)) {
                $this->droppedSpan++;
                return;
            }
        }

        //After transaction already stopped
        if ($trace instanceof Transaction) {
            //Include metrictset if it enabled
            if ($this->config->get('enableMetrics', false)) {
                $metricHelper = new MetricHelper();
                $metricset = new Metricset();
                foreach ($metricHelper->collectInformation() as $key => $value) {
                    $metric = new Metric($key, $value);
                    $metricset->put($metric);
                }
                $this->traces->register($metricset);
            }

            $trace->setStartedSpan($this->startedSpan);
            $trace->setDroppedSpan($this->droppedSpan);
            if ($this->sampleRateApplied) {
                $trace->setSampled(false);
            }
        }

        $this->traces->register($trace);
    }

    /**
     * Send Data to APM Service
     *
     * @see https://github.com/philkra/elastic-apm-laravel/issues/22
     * @see https://github.com/philkra/elastic-apm-laravel/issues/26
     *
     */
    public function send()
    {
        if (false === $this->traces->isEmpty()) {
            $this->httpClient->send($this->traces);
            $this->traces->reset();
            //This line used for some consumer command which never terminated and transaction start and end in the loop
            if (PHP_SAPI === 'cli') {
                $this->init();
            }
        }
    }
}
