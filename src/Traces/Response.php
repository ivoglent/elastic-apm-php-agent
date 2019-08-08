<?php


namespace PhilKra\Traces;


class Response implements Trace
{
    /**
     * @var bool
     */
    private $finished;

    /**
     * @var array
     */
    private $headers;

    /**
     * @var bool
     */
    private $headersSent;

    /**
     * @var integer
     */
    private $statusCode;

    /**
     * @return bool
     */
    public function isFinished(): bool
    {
        return $this->finished;
    }

    /**
     * @param bool $finished
     */
    public function setFinished(bool $finished): void
    {
        $this->finished = $finished;
    }

    /**
     * @return array
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * @param array $headers
     */
    public function setHeaders(array $headers): void
    {
        $this->headers = $headers;
    }

    /**
     * @return bool
     */
    public function isHeadersSent(): bool
    {
        return $this->headersSent;
    }

    /**
     * @param bool $headers_sent
     */
    public function setHeadersSent(bool $headersSent): void
    {
        $this->headersSent = $headersSent;
    }

    /**
     * @return int
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * @param int $status_code
     */
    public function setStatusCode(int $statusCode): void
    {
        $this->statusCode = $statusCode;
    }

    public function jsonSerialize()
    {
        return [
            'finished' => $this->finished,
            'headers' => $this->headers,
            'headers_sent' => $this->headersSent,
            'status_code' => $this->statusCode
        ];
    }
}