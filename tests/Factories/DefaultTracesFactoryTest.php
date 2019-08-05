<?php


namespace PhilKra\Tests\Factories;


use PhilKra\Factories\DefaultTracesFactory;
use PhilKra\Helper\Config;
use PhilKra\Traces\Error;
use PhilKra\Traces\Metadata;
use PhilKra\Traces\Metricset;
use PhilKra\Traces\Span;
use PhilKra\Traces\Transaction;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class DefaultTracesFactoryTest extends TestCase
{
    /**
     * @var DefaultTracesFactory | MockObject
     */
    private $factory;

    public function setUp()
    {
        $config = $this->createMock(Config::class);
        $this->factory = new DefaultTracesFactory($config);
    }

    public function testNewError() {
        $error = $this->factory->newError(new \Exception('Test'));
        $this->assertInstanceOf(Error::class, $error);
    }

    public function testNewSpan() {
        $span = $this->factory->newSpan('Test name', 'type');
        $this->assertInstanceOf(Span::class, $span);
    }

    public function testNewTransaction() {
        $transaction = $this->factory->newTransaction('Test name', 'test type');
        $this->assertInstanceOf(Transaction::class, $transaction);
    }

    public function testNewMetricset() {
        $metric = $this->factory->newMetricset();
        $this->assertInstanceOf(Metricset::class, $metric);
    }

    public function testNewMetadata() {
        $meta = $this->factory->newMetadata();
        $this->assertInstanceOf(Metadata::class, $meta);
    }
}