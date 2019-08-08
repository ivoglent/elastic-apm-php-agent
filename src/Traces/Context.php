<?php


namespace PhilKra\Traces;


class Context implements Trace
{
    /**
     * @var Request
     */
    private $request;

    public function setRequest(string  $url, string $method) {
        $request = new Request();
        $request->setMethod($method);
        $request->setUrl($url);
        $this->request = $request;
    }

    public function jsonSerialize()
    {
        return [
            'request' => $this->request
        ];
    }
}