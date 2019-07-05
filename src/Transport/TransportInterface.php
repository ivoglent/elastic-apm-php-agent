<?php


namespace PhilKra\Transport;


use PhilKra\Stores\TracesStore;

interface TransportInterface
{
    function send(TracesStore $store) : bool;
}