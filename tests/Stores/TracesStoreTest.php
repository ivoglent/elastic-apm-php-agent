<?php


namespace PhilKra\Tests\Stores;


use PhilKra\Factories\DefaultTracesFactory;
use PhilKra\Helper\Config;
use PhilKra\Helper\Timer;
use PhilKra\Stores\TracesStore;
use PhilKra\Traces\Trace;
use PhilKra\Traces\Transaction;
use PHPUnit\Framework\TestCase;

class TracesStoreTest extends TestCase
{
    public function testRegisterAndResetTrace() {
        $trace = $this->createMock(Trace::class);
        $traceStore = new TracesStore();
        $traceStore->register($trace);
        $this->assertFalse($traceStore->isEmpty());
        $this->assertSame([$trace], $traceStore->list());

        $traceStore->reset();
        $this->assertTrue($traceStore->isEmpty());
        $this->assertSame([], $traceStore->list());
    }

    public function testGetNDJson() {
        $traceStore = new TracesStore();

        $factory = new DefaultTracesFactory($this->createMock(Config::class));

        $transaction = $factory->newTransaction('TransactionName', 'TransactionId');

        $transaction->setTraceId('transactionTraceId');
        $transaction->setId('TransactionId');
        $transaction->start();

        $span = $factory->newSpan('test', 'test');
        $span->setId('TestSpanId');
        $span->setParentId('parentId');
        $span->setTraceId('traceId');
        $span->setTransaction($transaction);
        $span->start();

        $reflection = new \ReflectionClass($span);
        $reflectionProperty = $reflection->getProperty('timestamp');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($span, 123456789);

        $reflectionProperty = $reflection->getProperty('duration');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($span, 0);

        $reflectionProperty = $reflection->getProperty('start');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($span, 0);

        $traceStore->register($span);
        $expected = '{"span":{"id":"TestSpanId","action":null,"transaction_id":"TransactionId","trace_id":"transactionTraceId","start":0,"parent_id":"parentId","name":"test","type":"test","timestamp":123456789,"duration":0,"sync":false,"context":null,"stacktrace":null}}';
        $this->assertSame($expected, trim($traceStore->toNdJson()));

        $expectedSerilize = '[{"span":{"id":"TestSpanId","action":null,"transaction_id":"TransactionId","trace_id":"transactionTraceId","start":0,"parent_id":"parentId","name":"test","type":"test","timestamp":123456789,"duration":0,"sync":false,"context":null,"stacktrace":null}}]';
        $this->assertSame($expectedSerilize, \json_encode($traceStore->jsonSerialize()));

        $span->addStacktraces(['test']);

        $expected = '{"span":{"id":"TestSpanId","action":null,"transaction_id":"TransactionId","trace_id":"transactionTraceId","start":0,"parent_id":"parentId","name":"test","type":"test","timestamp":123456789,"duration":0,"sync":false,"context":null,"stacktrace":["test"]}}';
        $this->assertSame($expected, trim($traceStore->toNdJson()));

    }

    public function testGetTransaction() {
        $transaction = $this->createMock(Transaction::class);
        $traceStore = new TracesStore();
        $traceStore->register($transaction);

        $this->assertSame($transaction, $traceStore->getTransaction());
    }

    public function testGetTransactionNull() {
        $traceStore = new TracesStore();
        $this->assertNull($traceStore->getTransaction());
    }

}