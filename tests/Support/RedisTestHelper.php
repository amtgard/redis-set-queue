<?php

namespace Support;

use Redis;

trait RedisTestHelper
{
    protected function connectRedis(int $port = 36379): Redis
    {
        $redis = new Redis();
        try {
            if (!$redis->connect('127.0.0.1', $port, 1.0)) {
                $this->markTestSkipped('Redis connection not established');
            }
            $redis->ping();
        } catch (\RedisException) {
            $this->markTestSkipped('Redis connection not established');
        }
        return $redis;
    }

    protected function flushRedisKeys(Redis $redis, string ...$keys): void
    {
        try {
            foreach ($keys as $key) {
                $redis->del($key);
            }
        } catch (\RedisException) {
            $this->markTestSkipped('Redis connection not established');
        }
    }
}
