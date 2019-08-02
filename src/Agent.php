<?php
declare(strict_types=1);

/**
 * This file is part of the PhilKra/elastic-apm-php-agent library
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license http://opensource.org/licenses/MIT MIT
 * @link https://github.com/philkra/elastic-apm-php-agent GitHub
 */

namespace PhilKra;

use PhilKra\Helper\System;
use PhilKra\Stores\TracesStore;
use PhilKra\Factories\TracesFactory;
use PhilKra\Factories\DefaultTracesFactory;
use PhilKra\Traces\Trace;
use PhilKra\Helper\Timer;
use PhilKra\Helper\Config;
use PhilKra\Transport\TransportFactory;
use PhilKra\Transport\TransportInterface;

/**
 *
 * APM Agent
 *
 */
class Agent
{
    /**
     * Agent Version
     *
     * @var string
     */
    public const VERSION = '6.5.9-beta';

    /**
     * Agent Name
     *
     * @var string
     */
    public const NAME = 'php';

    /**
     * Config Store
     *
     * @var \PhilKra\Helper\Config
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
      'user'   => [],
      'custom' => [],
      'tags'   => []
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
     * Setup the APM Agent
     *
     * @param array $config
     * @param array $sharedContext Set shared contexts such as user and tags
     *
     */

    public function __construct(array $config, array $sharedContext = [])
    {
        // Init Agent Config
        $this->config = new Config($config);

        // Init http client
        $this->httpClient = TransportFactory::new($this->config);

        // Init the Traces Factory
        $this->factory = new DefaultTracesFactory($this->getConfig());

        // Init the Traces Store
        $this->traces = new TracesStore();

        // Generate Metadata Trace
        $metadata = $this->factory->newMetadata();
        $metadata->getUser()->initFromArray($sharedContext['user']);
        $this->register($metadata);

        // Init the Shared Context
        $this->sharedContext['custom'] = $sharedContext['custom'] ?? [];
        $this->sharedContext['tags']   = $sharedContext['tags'] ?? [];

        // Let's misuse the context to pass the environment variable and cookies
        // config to the EventBeans and the getContext method
        // @see https://github.com/philkra/elastic-apm-php-agent/issues/27
        // @see https://github.com/philkra/elastic-apm-php-agent/issues/30
        $this->sharedContext['env'] = $this->config->get('env', []);
        $this->sharedContext['cookies'] = $this->config->get('cookies', []);
        
        // Start Global Agent Timer
        $this->timer = new Timer();
        $this->timer->start();
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
    public function factory() : TracesFactory
    {
        return $this->factory;
    }

    /**
     * Get the Agent Config
     *
     * @return \PhilKra\Helper\Config
     */
    public function getConfig() : \PhilKra\Helper\Config
    {
        return $this->config;
    }

    /**
     * Put a Trace in the Registry
     *
     * @param Trace $trace
     *
     * @return void
     */
    public function register(Trace $trace) : void
    {
        $this->traces->register($trace);
    }

    /**
     * Send Data to APM Service
     *
     * @link https://github.com/philkra/elastic-apm-laravel/issues/22
     * @link https://github.com/philkra/elastic-apm-laravel/issues/26
     *
     * @return bool
     */
    public function send() : bool
    {
        $status = false;
        if ($this->traces->isEmpty() === false) {
            $status = $this->httpClient->send($this->traces);
            if ($status) {
                $this->traces->reset();
            }
        }
        return $status;
    }

}
