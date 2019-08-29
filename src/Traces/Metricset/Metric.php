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

namespace PhilKra\Traces\Metricset;

/**
 * APM Metric Object
 *
 * @see https://www.elastic.co/guide/en/apm/server/6.7/metricset-api.html#metricset-schema
 * @version 6.7 (v2)
 */
class Metric
{

    /** @var string */
    private $name;

    /** @var mixed */
    private $value;

    /**
     * @param string $name
     * @param mixed $value
     */
    public function __construct(string $name, $value)
    {
        $this->name = trim($name);
        $this->value = $value;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }
}
