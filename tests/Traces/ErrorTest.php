<?php


namespace PhilKra\Tests\Traces;


use PhilKra\Traces\Error;
use PhilKra\Traces\Transaction;
use PHPUnit\Framework\TestCase;

class ErrorTest extends TestCase
{
    public function testCreateError() {
        $exception = new \Exception('Test');
        $context = ['Test' => 'true'];
        $error = new Error($exception, $context);
        $this->assertSame($context, $error->getContext());
        $error->setTransactionId('1234');
        $this->assertSame('1234', $error->getTransactionId());
        $transaction = $this->createMock(Transaction::class);
        $error->setTransaction($transaction);
        $this->assertSame($transaction, $error->getTransaction());
        $traceBacks = $error->mapStacktrace($exception->getTrace());
        $this->assertSame($traceBacks, $error->getStacktrace());

        $this->assertArrayHasKey('error', $error->jsonSerialize());
    }
}