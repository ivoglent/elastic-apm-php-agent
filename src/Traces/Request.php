<?php


namespace PhilKra\Traces;


class Request implements Trace
{
    /**
     * @var string
     */
    private $method;

    /**
     * @var array
     */
    private $url;

    /**
     * @param string $method
     */
    public function setMethod(string $method): void
    {
        $this->method = $method;
    }

    /**
     * @param string $url
     */
    public function setUrl(string $url): void
    {
        $urlParts = parse_url($url);
        $this->url = [
            'raw' => $url,
            'protocol' => $urlParts['schema'] ?? 'http',
            'full' => $url,
            'hostname' => $urlParts['hostname'] ?? '',
            'port' => $urlParts['port'] ?? 80,
            'pathname' => $urlParts['path'] ?? '',
            'search' => $urlParts['query'] ?? '',
            'hash' => $urlParts['fragment'] ?? ''
        ];
    }

    public function jsonSerialize()
    {
        return [
            'method' => $this->method,
            'url' => $this->url
        ];
    }
}