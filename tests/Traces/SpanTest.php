<?php


namespace PhilKra\Tests\Traces;


use PhilKra\Traces\Span;
use PhilKra\Traces\SpanContexts\Http;
use PhilKra\Traces\Stacktrace;
use PhilKra\Traces\Transaction;
use PHPUnit\Framework\TestCase;

class SpanTest extends TestCase
{
    /** @var  Span */
    private $span;
    public function setUp()
    {
        parent::setUp();
        mt_srand(123456);
        $this->span = new Span('TestName', 'TestType');
        $transaction = new Transaction('TestTransactionName', 'TestTransactionType');
        $transaction->setTraceId('testTrace');
        $transaction->setId('testTransaction');
        $transaction->start();
        $this->span->setTransaction($transaction);
        $this->span->setTraceId('testTrace');
        $this->span->setId('testSpan');
        $this->span->start();

        $reflection = new \ReflectionClass($this->span);
        $reflectionProperty = $reflection->getProperty('timestamp');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($this->span, 123456789);

        $reflectionProperty = $reflection->getProperty('start');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($this->span, 1);
    }

    public function testCreateSpan() {
        $expectedPayload = [
            'span' => [
                'id' => 'testSpan',
                'action' => null,
                'transaction_id' => 'testTransaction',
                'trace_id' => 'testTrace',
                'start' => 1,
                'parent_id' => null,
                'name' => 'TestName',
                'type' => 'TestType',
                'timestamp' => 123456789,
                'duration' => null,
                'sync' => false,
                'context' => null,
                'stacktrace' => null
            ]
        ];

        $this->assertSame($expectedPayload, $this->span->jsonSerialize());
    }

    public function testSetAction() {
        $expectedPayload = [
            'span' => [
                'id' => 'testSpan',
                'action' => 'testAction',
                'transaction_id' => 'testTransaction',
                'trace_id' => 'testTrace',
                'start' => 1,
                'parent_id' => null,
                'name' => 'TestName',
                'type' => 'TestType',
                'timestamp' => 123456789,
                'duration' => null,
                'sync' => false,
                'context' => null,
                'stacktrace' => null
            ]
        ];
        $this->span->setAction('testAction');
        $this->assertSame($expectedPayload, $this->span->jsonSerialize());
    }

    public function testContext() {
        $httpContext = new Http('url',200, 'get');
        $this->span->addContext('http', $httpContext);
        $expectedPayload = [
            'span' => [
                'id' => 'testSpan',
                'action' => null,
                'transaction_id' => 'testTransaction',
                'trace_id' => 'testTrace',
                'start' => 1,
                'parent_id' => null,
                'name' => 'TestName',
                'type' => 'TestType',
                'timestamp' => 123456789,
                'duration' => null,
                'sync' => false,
                'context' => [
                    'http' => $httpContext
                ],
                'stacktrace' => null
            ]
        ];
        $this->assertSame($expectedPayload, $this->span->jsonSerialize());

    }

    public function testStacktrace() {
        $this->assertNull($this->span->getStacktrace());
        $this->span->addStacktrace($this->createMock(Stacktrace::class));
        $this->assertNotNull($this->span->getStacktrace());
        $stacktraces = [$this->createMock(Stacktrace::class)];
        $this->span->addStacktraces($stacktraces);
        $this->assertCount(1, $this->span->getStacktrace());
        $this->assertSame($stacktraces, $this->span->getStacktrace());
    }
}