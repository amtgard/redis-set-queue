<?php

namespace Integ;

use Amtgard\SetQueue\DataStructure\Impl\Redis\RedisDataStructureConfig;
use Amtgard\SetQueue\DataStructure\Impl\Redis\RedisHashSetFactory;
use Amtgard\SetQueue\DataStructure\Impl\Redis\RedisRedrivableQueueFactory;
use Amtgard\SetQueue\DataStructure\SetQueue;
use Amtgard\SetQueue\PubSubQueue;
use PHPUnit\Framework\TestCase;
use Redis;

class RedisPubSubQueueTest extends TestCase
{
    private Redis $redis;
    private RedishashSetFactory $hashSetFactory;
    private RedisRedrivableQueueFactory $redrivableQueueFactory;
    private SetQueue $queue;

    public function setUp(): void
    {
        parent::setUp();
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
        $this->queue = new SetQueue("TEST", $config, $this->hashSetFactory, $this->redrivableQueueFactory);
    }

    public function testPubSub() {
        $pubSub = new PubSubQueue();
        $pubSub->addQueue($this->queue);

        $callCount = 0;
        $handle = $pubSub->subscribe($this->queue->getName(), function($key, $message) use (&$callCount) {
            if ($message == "MESSAGE1") {
                $callCount++;
            }
        });
        $pubSub->send($handle, "KEY1", "MESSAGE1");
        $pubSub->send($handle, "KEY2", "MESSAGE2");
        $pubSub->send($handle, "KEY3", "MESSAGE1");
        $pubSub->pump($this->queue->getName());
        $pubSub->pump($this->queue->getName());
        $pubSub->pump($this->queue->getName());
        self::assertEquals(2, $callCount);
    }

    public function testWhenCallFails_thenFailureHandler() {
        $pubSub = new PubSubQueue();
        $pubSub->addQueue($this->queue);

        $callCount = 0;
        $handle = $pubSub->subscribe($this->queue->getName(), function($key, $message) use (&$callCount) {
            throw new \Exception($message);
        }, function($exception, $key, $message) use (&$callCount) {
            if ($message == "MESSAGE1") {
                $callCount++;
            }
        });
        $pubSub->send($handle, "KEY1", "MESSAGE1");
        $pubSub->send($handle, "KEY2", "MESSAGE2");
        $pubSub->send($handle, "KEY3", "MESSAGE1");
        $pubSub->pump($this->queue->getName());
        $pubSub->pump($this->queue->getName());
        $pubSub->pump($this->queue->getName());
        self::assertEquals(2, $callCount);
    }

}