<?php

namespace DataStructure\Impl\InMemory;

use Amtgard\SetQueue\DataStructure\Impl\InMemory\InMemoryHashSet;
use Amtgard\SetQueue\DataStructure\Impl\InMemory\InMemoryHashSetFactory;
use Amtgard\SetQueue\DataStructure\Impl\InMemory\InMemoryRedrivableQueue;
use Amtgard\SetQueue\DataStructure\Impl\InMemory\InMemoryRedrivableQueueFactory;
use Amtgard\SetQueue\DataStructure\Impl\Redis\RedisDataStructureConfig;
use PHPUnit\Framework\TestCase;

class InMemoryFactoryTest extends TestCase
{
    public function testHashSetFactoryCreatesInMemoryHashSet(): void
    {
        $factory = new InMemoryHashSetFactory();
        $config = new RedisDataStructureConfig();
        self::assertInstanceOf(InMemoryHashSet::class, $factory->create($config, 'TEST'));
    }

    public function testRedrivableQueueFactoryCreatesInMemoryRedrivableQueue(): void
    {
        $factory = new InMemoryRedrivableQueueFactory();
        $config = new RedisDataStructureConfig();
        self::assertInstanceOf(InMemoryRedrivableQueue::class, $factory->create($config, 'TEST'));
    }
}
