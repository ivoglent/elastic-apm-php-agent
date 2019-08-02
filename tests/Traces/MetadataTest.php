<?php


namespace PhilKra\Tests\Traces;


use PhilKra\Agent;
use PhilKra\Helper\Config;
use PhilKra\Traces\Metadata;
use PhilKra\Traces\Metadata\Process;
use PhilKra\Traces\Metadata\Service;
use PhilKra\Traces\Metadata\System;
use PhilKra\Traces\Metadata\User;
use PHPUnit\Framework\TestCase;

class MetadataTest extends TestCase
{
    public function testProcess() {
        $process = new Process();
        $this->assertArrayHasKey('pid', $process->jsonSerialize());
    }

    public function testService() {
        $configMap = [
            ['name', null, 'Test'],
            ['framework', null, '']
        ];
        $config = $this->createMock(Config::class);
        $config->expects(self::atLeastOnce())->method('get')->willReturnMap($configMap);
        $service = new Service($config);

        $expectedPayload = [
            'name' => 'Test',
            'version' => null,
            'agent' => [
                'name' => Agent::NAME,
                'version' => Agent::VERSION
            ],
            'language' => [
                'name' => 'php',
                'version' => phpversion()
            ],
            'environment' => null
        ];
        $this->assertSame($expectedPayload, $service->jsonSerialize());
    }

    public function testServiceWithError() {
        $config = $this->createMock(Config::class);
        $config->expects(self::atLeastOnce())->method('get')->willReturn(null);
        $this->expectException(\Exception::class);
        (new Service($config))->jsonSerialize();
    }

    public function testServiceWithFramework() {
        $configMap = [
            ['name', null, 'Test'],
            ['framework', null, [
                'name' => 'symfony'
            ]]
        ];
        $config = $this->getMockBuilder(Config::class)->disableOriginalConstructor()->setMethods(['get'])->getMock();
        $config->method('get')->willReturnMap($configMap);
        $service = new Service($config);
        $expectedPayload = [
            'name' => 'Test',
            'version' => null,
            'agent' => [
                'name' => Agent::NAME,
                'version' => Agent::VERSION
            ],
            'language' => [
                'name' => 'php',
                'version' => phpversion()
            ],
            'environment' => null,
            'framework' => [
                'name' => 'symfony'
            ]
        ];
        $this->assertSame($expectedPayload, $service->jsonSerialize());
    }

    public function testSystem() {
        $config = $this->createMock(Config::class);
        $config->expects(self::atLeastOnce())->method('get')->willReturn('test');
        $expectedPayload = [
            'hostname'     => 'test',
            'architecture' => php_uname('m'),
            'platform'     => php_uname('s')
        ];
        $system = new System($config);
        $this->assertSame($expectedPayload, $system->jsonSerialize());
    }

    public function testUserInitFromArray() {
        $config = $this->createMock(Config::class);
        $config->expects(self::any())->method('get')->willReturn('test');
        $expectedPayload = [
            'id'     => 'id',
            'username' => 'test',
            'email'     => 'test@gmail.com'
        ];
        $user = new User($config);
        $user->initFromArray($expectedPayload);
        $this->assertSame($expectedPayload, $user->jsonSerialize());
    }

    public function testUser() {
        $config = $this->createMock(Config::class);
        $config->expects(self::any())->method('get')->willReturn('test');
        $expectedPayload = [
            'id'     => 'id',
            'username' => 'test',
            'email'     => 'test@gmail.com'
        ];
        $user = new User($config);
        $user->setId('id');
        $user->setEmail('test@gmail.com');
        $user->setUsername('test');
        $this->assertSame($expectedPayload, $user->jsonSerialize());
        $this->assertTrue($user->isSet());
    }

    public function testMetadata() {
        /*$process = $this->createMock(Process::class);
        $service = $this->createMock(Service::class);
        $user = $this->createMock(User::class);
        $system = $this->createMock(System::class);*/

        $metadata = new Metadata($this->createMock(Config::class));
        $this->assertInstanceOf(Process::class, $metadata->getProcess());
        $this->assertInstanceOf(User::class, $metadata->getUser());
        $this->assertInstanceOf(Service::class, $metadata->getService());
        $this->assertInstanceOf(System::class, $metadata->getSystem());
        $expectedPayload = [
            'metadata' => [
                'process' => $metadata->getProcess(),
                'system'  => $metadata->getSystem(),
                'service' => $metadata->getService(),
            ]
        ];

        $this->assertSame($expectedPayload, $metadata->jsonSerialize());

        $user = $this->createMock(User::class);
        $user->expects(self::once())->method('isSet')->willReturn(true);

        $reflection = new \ReflectionClass($metadata);
        $reflectionProperty = $reflection->getProperty('user');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($metadata, $user);
        $expectedPayload['metadata']['user'] = $user;
        $this->assertSame($expectedPayload, $metadata->jsonSerialize());
    }
}