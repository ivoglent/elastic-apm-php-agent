<?php

namespace PhilKra\Transport;

use PhilKra\Stores\TracesStore;

interface TransportInterface
{
    public function send(TracesStore $store);
}
