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

use PhilKra\Traces\Metricset\Metric;

/**
 * APM Metric's
 *
 * @see https://www.elastic.co/guide/en/apm/server/6.7/metricset-api.html
 * @version 6.7 (v2)
 */
class Metricset extends TimedTrace
{

    /** @var array * */
    private $samples = [];

    public function __construct()
    {
        parent::__construct();
        $this->timestamp = $this->getTimer()->getNow();
    }

    /**
     * Put a Metric to the Set
     *
     * @param Metric $metric
     */
    public function put(Metric $metric)
    {
        $this->samples[$metric->getName()] = ['value' => $metric->getValue()];
    }

    /**
     * Serialize Metrics
     *
     * @return array
     */
    public function jsonSerialize(): array
    {
        $payload = [
            'metricset' => [
                'timestamp' => $this->timestamp,
                'samples' => $this->samples,
            ],
        ];
        return $payload;
    }
}
