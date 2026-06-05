<?php

namespace DataStructure\Impl\Redis;

use Amtgard\SetQueue\DataStructure\Impl\Redis\RedisDataStructureConfig;
use Amtgard\SetQueue\DataStructure\Impl\Redis\RedisHashSet;
use Amtgard\SetQueue\DataStructure\Impl\Redis\RedisHashSetFactory;
use Amtgard\SetQueue\DataStructure\Impl\Redis\RedisRedrivableQueue;
use Amtgard\SetQueue\DataStructure\Impl\Redis\RedisRedrivableQueueFactory;
use PHPUnit\Framework\TestCase;
use Support\RedisTestHelper;

class RedisFactoryTest extends TestCase
{
    use RedisTestHelper;

    private RedisDataStructureConfig $config;

    protected function setUp(): void
    {
        parent::setUp();
        $this->connectRedis();
        $this->config = new RedisDataStructureConfig();
        $this->config->setConfig(['host' => '127.0.0.1', 'port' => 36379]);
    }

    public function testHashSetFactoryCreatesRedisHashSet(): void
    {
        $factory = new RedisHashSetFactory();
        self::assertInstanceOf(RedisHashSet::class, $factory->create($this->config, 'FACTORY_TEST'));
    }

    public function testRedrivableQueueFactoryCreatesRedisRedrivableQueue(): void
    {
        $factory = new RedisRedrivableQueueFactory();
        self::assertInstanceOf(RedisRedrivableQueue::class, $factory->create($this->config, 'FACTORY_TEST'));
    }
}
