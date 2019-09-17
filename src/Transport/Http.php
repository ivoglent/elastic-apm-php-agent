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

namespace PhilKra\Transport;

use PhilKra\Agent;
use PhilKra\Helper\Config;
use PhilKra\Stores\TracesStore;

/**
 * Http Connector to the APM Server Endpoints
 */
class Http implements TransportInterface
{
    /**
     * @var Config
     */
    private $config;

    /** @var Curl */
    private $curl;

    /**
     * @param Config $config
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    /**
     * {@inheritdoc}
     */
    public function send(TracesStore $store)
    {
        $endpoint = sprintf('%s/intake/v2/events', $this->config->get('transport.host'));
        if (null === $this->curl) {
            // @codeCoverageIgnoreStart
            $this->curl = new Curl();
            // @codeCoverageIgnoreEnd
        }
        $data = $store->toNdJson();

        $this->curl->setOption(CURLOPT_URL, $endpoint);
        $this->curl->setOption(CURLOPT_RETURNTRANSFER, true);
        $this->curl->setOption(CURLOPT_HEADER, false);
        $this->curl->setOption(CURLOPT_NOSIGNAL, 1);
        $this->curl->setOption(CURLOPT_TIMEOUT_MS, (int) $this->config->get('transport.timeout', 3000));
        $this->curl->setOption(CURLINFO_HEADER_OUT, true);
        $this->curl->setOption(CURLOPT_POST, true);
        $this->curl->setOption(CURLOPT_POSTFIELDS, $data);

        $headers = $this->getRequestHeaders();
        $headers[] = 'Content-Length: '.strlen($data);

        $this->curl->setOption(CURLOPT_HTTPHEADER, $headers);
        $this->curl->execute();
        $this->curl->close();
        $this->curl = null;
    }

    /**
     * Get the Headers for the POST Request
     *
     * @return array
     */
    private function getRequestHeaders(): array
    {
        // Default Headers Set
        $headers = [
            'Content-Type: application/x-ndjson',
            'User-Agent:'.sprintf('apm-agent-php/%s', Agent::VERSION),
        ];

        // Add Secret Token to Header
        if (null !== $this->config->get('secretToken')) {
            $headers[] = 'Authorization: '.sprintf('Bearer %s', $this->config->get('secretToken'));
        }

        return $headers;
    }
}
