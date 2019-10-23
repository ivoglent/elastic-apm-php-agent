<?php

namespace PhilKra\Transport;

interface TransportInterface
{
    public function send(string $url, string $data, ?array $headers = [], ?int $timeout = 3000);
}
