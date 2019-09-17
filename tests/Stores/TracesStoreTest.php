<?php


namespace PhilKra\Tests\Stores;


use PhilKra\Exception\InvalidTimeException;
use PhilKra\Factories\DefaultTracesFactory;
use PhilKra\Helper\Config;
use PhilKra\Helper\Timer;
use PhilKra\Stores\TracesStore;
use PhilKra\Traces\Span;
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

    public function testRegisterWithException() {
        $transaction = $this->createMock(Transaction::class);
        $span = $this->createMock(Span::class);
        $store = new TracesStore();
        $store->register($transaction);
        $this->expectException(\RuntimeException::class);
        $store->register($span);
    }

    public function testValidateSuccess() {
        $store = new TracesStore();

        $span = $this->createMock(Span::class);
        $span->expects(self::any())->method('getTimestamp')->willReturn(1568707402);
        $span->expects(self::any())->method('getDuration')->willReturn(1);

        $transaction = $this->createMock(Transaction::class);
        $transaction->expects(self::any())->method('getTimestamp')->willReturn(1568707401);
        $transaction->expects(self::any())->method('getDuration')->willReturn(1);

        $store->register($span);
        $store->register($transaction);
        $this->assertTrue(true);
        $store->toNdJson();
    }

    /**
     * @dataProvider validationDataProvider
     */
    public function testValidation($txtInfo, $spanInfo, $message) {
        $store = new TracesStore();

        $span = $this->createMock(Span::class);
        $span->expects(self::any())->method('getTimestamp')->willReturn($spanInfo['timestamp']);
        $span->expects(self::any())->method('getDuration')->willReturn($spanInfo['duration']);

        $transaction = $this->createMock(Transaction::class);
        $transaction->expects(self::any())->method('getTimestamp')->willReturn($txtInfo['timestamp']);
        $transaction->expects(self::any())->method('getDuration')->willReturn($txtInfo['duration']);

        $store->register($span);
        $store->register($transaction);
        $this->expectException(InvalidTimeException::class);
        $this->expectExceptionMessage($message);

        $store->toNdJson();

    }

    public function validationDataProvider() {
        return [
            [
                'txtInfo' => [
                    'timestamp' => 1568707402,
                    'duration' => 1,
                ],
                'spanInfo' => [
                    'timestamp' => 1568707401,
                    'duration' => 1,
                ],
                'message' => 'Span timestamp can not be less than transaction timestamp'
            ],
            [
                'txtInfo' => [
                    'timestamp' => 1568707402,
                    'duration' => 1,
                ],
                'spanInfo' => [
                    'timestamp' => 1568707402,
                    'duration' => 2,
                ],
                'message' => 'Span duration can not be greater than transaction duration'
            ]

        ];
    }

}