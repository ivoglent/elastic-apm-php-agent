<?php


namespace PhilKra\Tests\Traces;


use PhilKra\Traces\Stacktrace;
use PHPUnit\Framework\TestCase;

class StacktraceTest extends TestCase
{
    public function testStacktrace() {
        $item = [
            'filename' => 'testfile',
            'lineno' => 1,
            'function' => 'test',
            'abs_path' => 'filepath'

        ];
        $stackTrace = new Stacktrace($item);
        $this->assertSame($item, $stackTrace->jsonSerialize());
    }
}