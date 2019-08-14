<?php


namespace PhilKra\Tests\Helper;


use PhilKra\Exception\InvalidConfigException;
use PhilKra\Helper\Config;
use PHPUnit\Framework\TestCase;

class ConfigTest extends TestCase
{
    public function testInvalidConfigName() {
        $this->expectException(InvalidConfigException::class);
        new Config([]);
    }

    public function testMinimumConfig() {
        $config = new Config(['name' => 'test']);
        $expectedConfig = [
            'transport'      => [
                'method' => 'http',
                'host'   => 'http://127.0.0.1:8200',
                'config' => [
                    'timeout' => 5,
                ],
            ],
            'secretToken'    => null,
            'hostname'       => gethostname(),
            'active'         => true,
            'environment'    => 'development',
            'env'            => [],
            'cookies'        => [],
            'backtraceLimit' => 0,
            'minimumSpanDuration' => 20,
            'maximumTransactionSpan' => 100,
            'sampleRate' => 0.1,
            'name' => 'test'
        ];
        $realConfig = $config->asArray();
        unset($realConfig['timestamp']);
        $this->assertSame($expectedConfig, $realConfig);
    }

    public function testGetConfig() {
        $config = new Config(['name' => 'test']);
        $this->assertSame('test', $config->get('name'));
        $this->assertSame('abc-value', $config->get('abc', 'abc-value'));
        $this->assertSame('abc-value', $config->get('', 'abc-value'));
        $this->assertSame('http', $config->get('transport.method'));
        $this->assertSame('test', $config->get('transport.nonexists', 'test'));
    }
}