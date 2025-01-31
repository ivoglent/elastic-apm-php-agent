<?php


namespace PhilKra\Tests\Traces;


use PhilKra\Traces\Context;
use PhilKra\Traces\Span;
use PhilKra\Traces\Transaction;
use PHPUnit\Framework\TestCase;

class TransactionTest extends TestCase
{
    /** @var  Transaction */
    private $transaction;
    public function setUp()
    {
        parent::setUp();
        mt_srand(123456);
        $this->transaction = new Transaction('testName', 'testType');
        $this->transaction->setId('testTransaction');
        $this->transaction->setTraceId('testTrace');
    }

    public function testNameNadType() {
        $this->assertSame('testName', $this->transaction->getName());
        $this->transaction->setName('testName2');
        $this->assertSame('testName2', $this->transaction->getName());

        $this->assertSame('testType', $this->transaction->getType());
        $this->transaction->setType('testType2');
        $this->assertSame('testType2', $this->transaction->getType());
    }

    public function testSetResults() {
        $result = 'testResult';
        $this->transaction->setResult($result);
        $reflection = new \ReflectionClass($this->transaction);
        $reflectionProperty = $reflection->getProperty('result');
        $reflectionProperty->setAccessible(true);
        $this->assertSame($result, $reflectionProperty->getValue($this->transaction));
    }

    public function testStop() {
        $this->transaction->start();
        $result = 'testResult';
        $this->transaction->stop($result);
        $reflection = new \ReflectionClass($this->transaction);
        $reflectionProperty = $reflection->getProperty('result');
        $reflectionProperty->setAccessible(true);
        $this->assertSame($result, $reflectionProperty->getValue($this->transaction));
    }

    public function testSetContext() {
        $context = $this->createMock(Context::class);
        $this->transaction->setContext($context);
        $reflection = new \ReflectionClass($this->transaction);
        $reflectionProperty = $reflection->getProperty('context');
        $reflectionProperty->setAccessible(true);
        $this->assertSame($context, $reflectionProperty->getValue($this->transaction));
    }

    public function testSetSample() {
        $this->transaction->setSampled(true);
        $this->assertTrue($this->transaction->getSampled());
        $this->transaction->setSampled(false);
        $this->assertFalse($this->transaction->getSampled());
    }

    public function testDropSpan() {
        $reflection = new \ReflectionClass($this->transaction);
        $reflectionProperty = $reflection->getProperty('droppedSpan');
        $reflectionProperty->setAccessible(true);
        $this->assertEquals(0, $reflectionProperty->getValue($this->transaction));
        $this->transaction->setDroppedSpan(1);
        $this->assertEquals(1, $reflectionProperty->getValue($this->transaction));
    }

    public function testStartedSpan() {
        $reflection = new \ReflectionClass($this->transaction);
        $reflectionProperty = $reflection->getProperty('startedSpan');
        $reflectionProperty->setAccessible(true);
        $this->assertEquals(0, $reflectionProperty->getValue($this->transaction));
        $this->transaction->setStartedSpan(1);
        $this->assertEquals(1, $reflectionProperty->getValue($this->transaction));
    }

    public function testSerialize() {
        $data = $this->transaction->jsonSerialize();
        $data['transaction']['timestamp'] = null;
        $this->assertSame([
            'transaction' => [
                'id' => 'testTransaction',
                'trace_id' => 'testTrace',
                'result' => null,
                'name' => 'testName',
                'type' => 'testType',
                'timestamp' => null,
                'duration' => null,
                'sampled' => true,
                'span_count' => [
                    'started' => 0,
                    'dropped' => 0
                ],
                'context' => null
            ]
        ], $data);
    }
}