<?php

namespace DataStructure\Impl\Redis;

use Amtgard\SetQueue\DataStructure\Impl\Redis\RedisDataStructureConfig;
use PHPUnit\Framework\TestCase;

class RedisDataStructureConfigTest extends TestCase
{
    public function testSetConfigWithArray(): void
    {
        $config = new RedisDataStructureConfig();
        $config->setConfig(['host' => '127.0.0.1', 'port' => 36379]);
        self::assertEquals(['host' => '127.0.0.1', 'port' => 36379], $config->getConfig());
    }

    public function testSetConfigWithKeyValue(): void
    {
        $config = new RedisDataStructureConfig();
        $config->setConfig(['host' => '127.0.0.1', 'port' => 36379]);
        $config->setConfig('timeout', 5);
        self::assertEquals(5, $config->getConfig()['timeout']);
    }

    public function testSetConfigWithoutHostThrows(): void
    {
        $config = new RedisDataStructureConfig();
        $this->expectException(\InvalidArgumentException::class);
        $config->setConfig(['port' => 36379]);
    }

    public function testSetConfigWithoutPortThrows(): void
    {
        $config = new RedisDataStructureConfig();
        $this->expectException(\InvalidArgumentException::class);
        $config->setConfig(['host' => '127.0.0.1']);
    }
}
