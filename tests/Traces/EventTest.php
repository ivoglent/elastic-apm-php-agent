<?php


namespace PhilKra\Tests\Traces;


use PhilKra\Traces\Event;
use PhilKra\Traces\Transaction;
use PHPUnit\Framework\TestCase;

class EventTest extends TestCase
{
    /**
     * @var Event
     */
    private $event;
    public function setUp()
    {
        parent::setUp();
        mt_srand(123456);
        $this->event = new Event();
        $this->event->generateId();
        $this->event->setTraceId($this->event->generateTraceId());
    }

    public function testEventId() {
        $this->assertEquals(36, strlen($this->event->getId()));
        $this->event->setId('id');
        $this->assertSame('id', $this->event->getId());
    }

    public function testEventTraceId() {
        $this->assertEquals(36, strlen($this->event->getTraceId()));
        $this->event->setTraceId('traceId');
        $this->assertSame('traceId', $this->event->getTraceId());
    }

    public function testEventParentId() {
        $this->assertNull($this->event->getParentId());
        $this->event->setParentId('parentId');
        $this->assertSame('parentId', $this->event->getParentId());
    }

    public function testEventTransaction() {
        $transaction = $this->createMock(Transaction::class);
        $this->event->setTransaction($transaction);
        $reflection = new \ReflectionClass($this->event);
        $reflectionProperty = $reflection->getProperty('transaction');
        $reflectionProperty->setAccessible(true);
        $this->assertSame($transaction, $reflectionProperty->getValue($this->event));
    }
}