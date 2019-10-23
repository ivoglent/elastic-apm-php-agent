<?php


namespace PhilKra\Tests\Transport;


use GuzzleHttp\Client;
use PhilKra\Helper\Config;
use PhilKra\Stores\TracesStore;
use PhilKra\Transport\Http;
use PhilKra\Transport\TransportFactory;
use PHPUnit\Framework\TestCase;

class TransportFactoryTest extends TestCase
{
    public function testFactoryWithGuzzleClient() {
        $configMap = [
            ['transport', null, ['method' =>'http']],
            ['transport.host', null, 'http://localhost'],
        ];
        $config = $this->createMock(Config::class);
        $config->expects(self::atLeastOnce())->method('get')->willReturnMap($configMap);
        $result = TransportFactory::new($config);
        $this->assertInstanceOf(Http::class, $result);
    }

    public function testFactoryWithOtherClient() {
        $config = $this->createMock(Config::class);
        $config->expects(self::atLeastOnce())->method('get')->willReturn([
            'method' => 'other',
            'client' =>  $this->getFakeClient()
        ]);
        $result = TransportFactory::new($config);
        $this->assertNotInstanceOf(Client::class, $result);
    }

    public function testFactoryWithException() {
        $config = $this->createMock(Config::class);
        $config->expects(self::atLeastOnce())->method('get')->willReturn([
            'method' => 'other'
        ]);
        $this->expectException(\RuntimeException::class);
        $result = TransportFactory::new($config);
    }

    private function getFakeClient() {
        return new class implements \PhilKra\Transport\TransportInterface {

            function send(string $url, string $data, ?array $headers = [], ?int $timeout = 3000): bool
            {
                // TODO: Implement send() method.
            }
        };
    }
}