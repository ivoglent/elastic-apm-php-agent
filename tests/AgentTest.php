<?php
namespace PhilKra\Tests;

use PhilKra\Agent;
use PhilKra\Factories\TracesFactory;
use PhilKra\Helper\Config;
use PhilKra\Stores\TracesStore;
use PhilKra\Traces\Trace;
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
            'version' => '1.0'
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

    public function testSendWithRegisteredTraces() {
        $this->traceStore->expects(self::once())->method('isEmpty')->willReturn(false);
        $this->transport->expects(self::once())->method('send')->willReturn(true);
        $this->assertTrue($this->agent->send());
    }


}
