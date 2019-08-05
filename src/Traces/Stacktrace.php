<?php

namespace PhilKra\Traces;

class Stacktrace implements Trace
{
    /**
     * Basename of tracing file
     *
     * @var string
     */
    private $filename;

    /**
     * Line number
     *
     * @var int
     */
    private $lineno;

    /**
     * Called function
     *
     * @var string
     */
    private $function;

    /**
     * Absolute path to file
     *
     * @var string
     */
    private $abs_path;

    public function __construct($configs = [])
    {
        foreach ($configs as $key => $value) {
            $this->{$key} = $value;
        }
    }

    /**
     * Serialize Span
     *
     * @return array
     */
    public function jsonSerialize(): array
    {
        return [
            'filename' => $this->filename,
            'lineno' => $this->lineno,
            'function' => $this->function,
            'abs_path' => $this->abs_path,
        ];
    }
}
