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

namespace PhilKra\Helper;

use PhilKra\Exception\InvalidConfigException;

/**
 * Agent Config Store
 */
class Config
{

    /**
     * Config Set
     *
     * @var array
     */
    private $config;

    /**
     * @param array $config
     * @throws InvalidConfigException
     */
    public function __construct(array $config)
    {
        if (false === isset($config['name'])) {
            throw new InvalidConfigException();
        }

        $this->config = array_replace_recursive($this->getDefaultConfig(), $config);
    }

    /**
     * Get Config Value
     *
     * @param string $key
     * @param mixed $default
     *
     * @return mixed: value | null
     */
    public function get(string $key, $default = null)
    {
        return $this->getValueByKey($key, $this->asArray(), $default);
    }

    /**
     * Get the all Config Set as array
     *
     * @return array
     */
    public function asArray(): array
    {
        return $this->config;
    }

    /**
     * Get the Default Config of the Agent
     *
     * @see https://github.com/philkra/elastic-apm-php-agent/issues/55
     *
     * @return array
     */
    private function getDefaultConfig(): array
    {
        return [
            'transport' => [
                'method' => 'http',
                'host' => 'http://127.0.0.1:8200',
                'config' => [
                    'timeout' => 5,
                ],
            ],
            'secretToken' => null,
            'hostname' => gethostname(),
            'active' => true,
            'environment' => 'development',
            'env' => [],
            'cookies' => [],
            'backtraceLimit' => 0,
            'minimumSpanDuration' => 20,
            'maximumTransactionSpan' => 100,
            'sampleRate' => 0.1
        ];
    }

    /**
     * Allow access to the Config with the dot.notation
     *
     * @credit Selvin Ortiz
     * @see https://selvinortiz.com/blog/traversing-arrays-using-dot-notation
     *
     * @param string $key
     * @param mixed $default
     *
     * @return mixed
     */
    private function getValueByKey($key, array $data, $default = null)
    {
        // @assert $key is a non-empty string
        // @assert $data is a loopable array
        // @otherwise return $default value
        if (!is_string($key) || empty($key) || !count($data)) {
            return $default;
        }

        // @assert $key contains a dot notated string
        if (false !== strpos($key, '.')) {
            $keys = explode('.', $key);

            foreach ($keys as $innerKey) {
                // @assert $data[$innerKey] is available to continue
                // @otherwise return $default value
                if (!array_key_exists($innerKey, $data)) {
                    return $default;
                }

                $data = $data[$innerKey];
            }

            return $data;
        }

        // @fallback returning value of $key in $data or $default value
        return array_key_exists($key, $data) ? $data[$key] : $default;
    }
}
