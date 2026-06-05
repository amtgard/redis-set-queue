<?php

use Amtgard\SetQueue\DataStructure\HashSetFactoryInterface;
use Amtgard\SetQueue\DataStructure\Impl\Redis\RedisDataStructureConfig;
use Amtgard\SetQueue\DataStructure\Impl\Redis\RedisHashSetFactory;
use Amtgard\SetQueue\DataStructure\Impl\Redis\RedisRedrivableQueueFactory;
use Amtgard\SetQueue\DataStructure\RedrivableQueueFactoryInterface;
use Amtgard\SetQueue\DataStructure\SetQueue;
use Amtgard\SetQueue\PubSubQueue;
use PHPUnit\Framework\TestCase;
use Support\RedisTestHelper;
use function PHPUnit\Framework\assertEquals;

class ProducerConsumerTest extends TestCase {
    use RedisTestHelper;

    public function testProducerConsumer() {
        $config = new RedisDataStructureConfig();
        $config->setConfig([
            'host' => '127.0.0.1',
            'port' => 36379,
        ]);
        $redis = $this->connectRedis();
        $this->flushRedisKeys($redis, 'test:set', 'test:queue', 'test:redrive');
        $hashSetFactory = new RedisHashSetFactory();
        $redrivableQueueFactory = new RedisRedrivableQueueFactory();
        $queue = new SetQueue("test", $config, $hashSetFactory, $redrivableQueueFactory);

        $pubSubQueue = new PubSubQueue();
        $pubSubQueue->addQueue("test", $queue);
        $pubSubQueue->publish("test", "KEY1", "MESSAGE1");
        $callCount = 0;
        $pubSubQueue->subscribe("test", function ($key, $message) use (&$callCount) {
           assertEquals("KEY1", $key);
           assertEquals("MESSAGE1", $message);
           $callCount++;
        });
        $pubSubQueue->callConsumers("test");
        $pubSubQueue->callConsumers("test");
        assertEquals(1, $callCount);
    }
}