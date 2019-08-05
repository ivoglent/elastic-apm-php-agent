<?php


namespace PhilKra\Transport;

/**
 * @codeCoverageIgnore
 */
class Curl
{
    private $handle = null;

    public function __construct() {
        $this->handle = curl_init();
    }

    public function setOption($name, $value) {
        curl_setopt($this->handle, $name, $value);
    }

    public function execute() {
        return curl_exec($this->handle);
    }

    public function close() {
        if ($this->handle) {
            curl_close($this->handle);
        }
    }
}