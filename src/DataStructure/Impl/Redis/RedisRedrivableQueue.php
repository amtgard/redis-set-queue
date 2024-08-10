<?php

namespace Amtgard\SetQueue\DataStructure\Impl\Redis;

use Amtgard\SetQueue\DataStructure\RedrivableQueue;
use Redis;

class RedisRedrivableQueue implements RedrivableQueue
{
    private String $queueKey;

    private String $redriveQueueKey;

    private Redis $redis;

    public function __construct(String $queuPrefix, Redis $redis) {
        $this->queueKey = "$queuPrefix:queue";
        $this->redriveQueueKey = "$queuPrefix:redrive";
        $this->redis = $redis;
    }

    public function enqueue(string $entry)
    {
        return $this->redis->lpush($this->queueKey, $entry);
    }

    public function dequeue(int $count = 1): array
    {
        $this->redis->watch($this->queueKey);
        $value = null;
        if (count($this->redis->lRange($this->queueKey, 0, 0)) > 0) {
            $value = $this->redis->rPopLPush($this->queueKey, $this->redriveQueueKey);
        }
        return $value ? [$value] : [];
    }

    public function redrive()
    {
        foreach ($this->redis->lRange($this->redriveQueueKey, 0, -1) as $key) {
            $this->redis->rPopLPush($this->redriveQueueKey, $this->queueKey);
        }
    }

    public function commit(string $entry)
    {
        return $this->redis->lrem($this->redriveQueueKey, $entry, 0);
    }
}