<?php
namespace PhilKra\Tests;

use PhilKra\Agent;
use PhilKra\Factories\TracesFactory;
use PhilKra\Helper\Config;
use PhilKra\Stores\TracesStore;
use PhilKra\Traces\Span;
use PhilKra\Traces\Trace;
use PhilKra\Traces\Transaction;
use PhilKra\Transport\TransportInterface;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Test Case for @see \PhilKra\Agent
 */
class AgentTest extends TestCase {
    /** @var  Agent */
    private $agent;

    /** @var  TracesStore|MockObject */
    private $traceStore;

    /** @var  TransportInterface|MockObject */
    private $transport;

    public function setUp()
    {
        parent::setUp();
        $this->agent = new Agent([
            'name' => 'unit-test',
            'version' => '1.0',
            'secretToken' => 'test'
        ], [
            'user' => []
        ]);

        $this->traceStore = $this->createMock(TracesStore::class);
        $this->setProxyValue($this->agent, 'traces', $this->traceStore);

        $this->transport = $this->createMock(TransportInterface::class);
        $this->setProxyValue($this->agent, 'httpClient', $this->transport);

    }

    public function testModifyFactory() {
        $factory = $this->createMock(TracesFactory::class);
        $this->agent->setFactory($factory);
        $this->assertSame($factory, $this->agent->factory());
    }

    public function testGetConfig() {
        $this->assertInstanceOf(Config::class, $this->agent->getConfig());
    }

    public function testTransactionRegister() {
        $trace = $this->createMock(Transaction::class);
        $this->traceStore->expects(self::once())->method('register')->with($trace);
        $this->agent->register($trace);
    }

    public function testRegister() {
        $this->setAgentSampleRate(false);
        $trace = $this->createMock(Span::class);
        $trace->expects(self::once())->method('getDuration')->willReturn(100);
        $this->traceStore->expects(self::once())->method('register')->with($trace);
        $this->agent->register($trace);
    }

    public function testRegisterDropSpanByDuration() {
        $this->setAgentSampleRate(false);
        $trace = $this->createMock(Span::class);
        $trace->expects(self::once())->method('getDuration')->willReturn(1);
        $this->traceStore->expects(self::never())->method('register')->with($trace);
        $this->agent->register($trace);
    }

    public function testRegisterDropSpanBySampleRate() {
        $this->setAgentSampleRate(true);
        $trace = $this->createMock(Span::class);
        $this->traceStore->expects(self::never())->method('register')->with($trace);
        $this->agent->register($trace);
    }

    public function testRegisterDropSpanByFull() {
        $trace = $this->createMock(Span::class);
        $trace->expects(self::once())->method('getDuration')->willReturn(100);
        $this->traceStore->expects(self::once())->method('list')->willReturn(array_fill(0,101, 1));
        $this->traceStore->expects(self::never())->method('register')->with($trace);
        $this->agent->register($trace);
    }

    public function testSendWithRegisteredTraces() {
        $this->traceStore->expects(self::once())->method('isEmpty')->willReturn(false);
        $this->traceStore->expects(self::once())->method('reset');
        $this->agent->send();
    }

    public function testRegisterWithMetric() {
        /** @var Transaction|MockObject $transaction */
        $transaction = $this->createMock(Transaction::class);
        $config = $this->agent->getConfig();
        $configData = $config->asArray();
        $configData['enableMetrics'] = true;

        $config = $this->setProxyValue($config, 'config', $configData);
        $this->setProxyValue($this->agent, 'config', $config);
        $this->setProxyValue($this->agent, 'sampleRateApplied', true);

        $this->traceStore->expects(self::exactly(2))->method('register');
        $this->agent->register($transaction);
        $this->assertFalse($transaction->getSampled());
    }

    private function setAgentSampleRate($value) {
        return $this->setProxyValue($this->agent, 'sampleRateApplied', $value);
    }

    private function setProxyValue($object, $name, $value) {
        $reflection = new \ReflectionClass($object);
        $reflectionProperty = $reflection->getProperty($name);
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($object, $value);
        return $object;
    }
}
