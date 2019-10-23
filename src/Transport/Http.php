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

use PhilKra\Helper\Config;

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
    public function send(string $url, string $data, ?array $headers = [], ?int $timeout = 3000)
    {
        if (null === $this->curl) {
            // @codeCoverageIgnoreStart
            $this->curl = new Curl();
            // @codeCoverageIgnoreEnd
        }
        $this->curl->setOption(CURLOPT_URL, $url);
        $this->curl->setOption(CURLOPT_RETURNTRANSFER, true);
        $this->curl->setOption(CURLOPT_HEADER, false);
        $this->curl->setOption(CURLOPT_NOSIGNAL, 1);
        $this->curl->setOption(CURLOPT_TIMEOUT_MS, $timeout);
        $this->curl->setOption(CURLINFO_HEADER_OUT, true);
        $this->curl->setOption(CURLOPT_POST, true);
        $this->curl->setOption(CURLOPT_POSTFIELDS, $data);

        $headers[] = 'Content-Length: '.strlen($data);

        $this->curl->setOption(CURLOPT_HTTPHEADER, $headers);
        $this->curl->execute();
        $this->curl->close();
        $this->curl = null;
    }
}
