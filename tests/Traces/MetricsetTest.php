<?php


namespace PhilKra\Tests\Traces;


use PhilKra\Traces\Metricset;
use PHPUnit\Framework\TestCase;

class MetricsetTest extends TestCase
{
    public function testMetric() {
        $metric = new Metricset\Metric('testName', 1);
        $this->assertSame('testName', $metric->getName());
        $this->assertSame(1, $metric->getValue());
    }

    public function testMetricset() {
        $metric = new Metricset\Metric('testName', 1);
        $metricset = new Metricset();

        $reflection = new \ReflectionClass($metricset);
        $reflectionProperty = $reflection->getProperty('timestamp');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($metricset, 123456789);

        $metricset->put($metric);

        $expectedPayload = [
            'metricset' => [
                'timestamp' => 123456789,
                'samples'   => [
                    $metric->getName() => [
                        'value' => $metric->getValue()
                    ]
                ],
            ]
        ];
        $this->assertSame($expectedPayload, $metricset->jsonSerialize());
    }
}