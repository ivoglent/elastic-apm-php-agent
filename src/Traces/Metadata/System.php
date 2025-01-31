<?php

namespace PhilKra\Traces\Metadata;

use PhilKra\Helper\Config;
use PhilKra\Traces\Trace;

/**
 * APM Metadata
 *
 * @see https://www.elastic.co/guide/en/apm/server/6.7/metadata-api.html#metadata-system-schema
 * @version 6.7 (v2)
 */
final class System implements Trace
{

    /**
     * @var Config
     */
    private $config;

    /**
     * @param Config $config
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    /**
     * {@inheritdoc}
     */
    public function jsonSerialize(): array
    {
        return [
           'hostname' => $this->config->get('hostname'),
           'architecture' => php_uname('m'),
           'platform' => php_uname('s'),
       ];
    }
}
