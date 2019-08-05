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
 * Connector Factory for the creation of the Agent to Server Connection
 */
class TransportFactory
{

    /**
     * Create Connector Instance based on the Config settings
     *
     * @param \PhilKra\Helper\Config $config
     *
     * @return TransportInterface
     */
    public static function new(Config $config): TransportInterface
    {
        // Read the Config
        $transport = $config->get('transport');
        $method = strtolower($transport['method']);

        // Http Transport Handler
        if ('http' === $method) {
            return new Http($config);
        }
        if (empty($transport['client']) || !($transport['client'] instanceof TransportInterface)) {
            throw new \RuntimeException('Invalid transport client for APM');
        }

        return $transport['client'];
    }
}
