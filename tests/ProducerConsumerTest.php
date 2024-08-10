<?php

use Amtgard\SetQueue\DataStructure\HashSetFactory;
use Amtgard\SetQueue\DataStructure\Impl\Redis\RedisDataStructureConfig;
use Amtgard\SetQueue\DataStructure\Impl\Redis\RedisHashSetFactory;
use Amtgard\SetQueue\DataStructure\Impl\Redis\RedisRedrivableQueueFactory;
use Amtgard\SetQueue\DataStructure\RedrivableQueueFactory;
use Amtgard\SetQueue\DataStructure\SetQueue;
use Amtgard\SetQueue\PubSubQueue;
use PHPUnit\Framework\TestCase;
use function PHPUnit\Framework\assertEquals;

class ProducerConsumerTest extends TestCase {
    private SetQueue $queue;
    private HashSetFactory $hashSetFactory;
    private RedrivableQueueFactory $redrivableQueueFactory;
    private Redis $redis;

    public function testProducerConsumer() {
        $config = new RedisDataStructureConfig();
        $config->setConfig([
            'host' => '127.0.0.1',
            'port' => 36379,
        ]);
        $this->redis = new Redis();
        $this->redis->pconnect($config->getConfig()['host'], $config->getConfig()['port']);
        if ($this->redis->isConnected()) {
            $this->redis->del("TEST:set");
            $this->redis->del("TEST:queue");
            $this->redis->del("TEST:redrive");
        }
        $this->hashSetFactory = new RedisHashSetFactory();
        $this->redrivableQueueFactory = new RedisRedrivableQueueFactory();
        $this->queue = new SetQueue("test", $config, $this->hashSetFactory, $this->redrivableQueueFactory);

        $pubSubQueue = new PubSubQueue();
        $pubSubQueue->addQueue($this->queue);
        $pubSubQueue->send("test", "KEY1", "MESSAGE1");
        $callCount = 0;
        $pubSubQueue->subscribe("test", function ($key, $message) use (&$callCount) {
           assertEquals("KEY1", $key);
           assertEquals("MESSAGE1", $message);
           $callCount++;
        });
        $pubSubQueue->pump("test");
        $pubSubQueue->pump("test");
        assertEquals(1, $callCount);
    }
}