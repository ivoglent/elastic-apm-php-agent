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
            'sampleRate' => 1.0,
            'minimumSpanDuration' => 20,
            'maximumTransactionSpan' => 2
        ], [
            'user' => []
        ]);

        $this->traceStore = $this->createMock(TracesStore::class);
        $reflection = new \ReflectionClass($this->agent);
        $reflectionProperty = $reflection->getProperty('traces');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($this->agent, $this->traceStore);

        $this->transport = $this->createMock(TransportInterface::class);
        $reflectionProperty = $reflection->getProperty('httpClient');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($this->agent, $this->transport);
    }

    public function testModifyFactory() {
        $factory = $this->createMock(TracesFactory::class);
        $this->agent->setFactory($factory);
        $this->assertSame($factory, $this->agent->factory());
    }

    public function testGetConfig() {
        $this->assertInstanceOf(Config::class, $this->agent->getConfig());
    }

    public function testRegister() {
        $trace = $this->createMock(Trace::class);
        $this->traceStore->expects(self::once())->method('register')->with($trace);
        $this->agent->register($trace);
    }

    public function testRegisterSpanMinDuration() {
        $trace = $this->createMock(Span::class);
        $trace->expects(self::once())->method('getDuration')->willReturn(10);
        $transaction = $this->createMock(Transaction::class);
        $this->traceStore->expects(self::atLeastOnce())->method('getTransaction')->willReturn($transaction);
        $this->traceStore->expects(self::never())->method('register')->with($trace);
        $this->agent->register($trace);
    }

    public function testRegisterSpanMaxSpan() {
        $trace = $this->createMock(Span::class);
        $this->traceStore->expects(self::once())->method('list')->willReturn([1,2,3]);
        $transaction = $this->createMock(Transaction::class);
        $this->traceStore->expects(self::atLeastOnce())->method('getTransaction')->willReturn($transaction);
        $trace->expects(self::once())->method('getDuration')->willReturn(30);
        $this->traceStore->expects(self::never())->method('register')->with($trace);
        $this->agent->register($trace);
    }

    public function testRegisterSpans() {

        $trace = $this->createMock(Span::class);
        $trace->expects(self::once())->method('getDuration')->willReturn(20);
        $transaction = $this->createMock(Transaction::class);
        $this->traceStore->expects(self::atLeastOnce())->method('getTransaction')->willReturn($transaction);
        $this->traceStore->expects(self::once())->method('list')->willReturn([1]);
        $this->traceStore->expects(self::once())->method('register')->with($trace);

        $this->agent->register($trace);
    }

    public function testSendWithRegisteredTraces() {
        $this->traceStore->expects(self::once())->method('isEmpty')->willReturn(false);
        $this->traceStore->expects(self::once())->method('reset');
        $this->agent->send();
    }

    public function testSampleRateApplied() {
        $reflection = new \ReflectionClass($this->agent);
        $reflectionProperty = $reflection->getProperty('sampleRateApplied');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($this->agent, true);
        $trace = $this->createMock(Span::class);
        $trace->expects(self::never())->method('getDuration')->willReturn(30);
        $transaction = $this->createMock(Transaction::class);
        $this->traceStore->expects(self::atLeastOnce())->method('getTransaction')->willReturn($transaction);
        $this->traceStore->expects(self::never())->method('register')->with($trace);

        $this->agent->register($trace);
    }




}
