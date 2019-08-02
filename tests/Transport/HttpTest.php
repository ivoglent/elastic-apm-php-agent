<?php


namespace PhilKra\Tests\Transport;


use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Psr7\Response;
use PhilKra\Helper\Config;
use PhilKra\Stores\TracesStore;
use PhilKra\Transport\Http;
use PHPUnit\Framework\TestCase;

class HttpTest extends TestCase
{
    /** @var  Http */
    private $http;
    public function setUp() {
        $configMap = [
            ['secretToken', null, 'testToken'],
            ['transport.config', null, []]
        ];
        $config = $this->createMock(Config::class);
        $config->expects(self::atLeastOnce())->method('get')->willReturnMap($configMap);
        $this->http = new Http($config);
    }

    public function testCreateHttpClient() {
        $reflection = new \ReflectionClass($this->http);
        $reflectionProperty = $reflection->getProperty('client');
        $reflectionProperty->setAccessible(true);
        $this->assertInstanceOf(ClientInterface::class, $reflectionProperty->getValue($this->http));
    }

    public function testCreateHttpClientWithExisted() {
        $client = $this->createMock(Client::class);
        $config = $this->createMock(Config::class);
        $this->http = new Http($config, $client);
        $reflection = new \ReflectionClass($this->http);
        $reflectionProperty = $reflection->getProperty('client');
        $reflectionProperty->setAccessible(true);
        $this->assertSame($client, $reflectionProperty->getValue($this->http));
    }

    public function testSend() {
        $client = $this->createMock(Client::class);
        $client->expects(self::once())->method('send')->willReturn($this->createMock(Response::class));
        $reflection = new \ReflectionClass($this->http);
        $reflectionProperty = $reflection->getProperty('client');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($this->http, $client);

        $tracesStore = $this->createMock(TracesStore::class);
        $tracesStore->expects(self::once())->method('toNdJson')->willReturn('testData');

        $this->http->send($tracesStore);
    }
}