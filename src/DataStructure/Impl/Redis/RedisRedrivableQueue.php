<?php

namespace Amtgard\SetQueue\DataStructure\Impl\Redis;

use Amtgard\Interface\EntryInterface;
use Amtgard\Interface\RedrivableQueueInterface;
use Amtgard\SetQueue\DataStructure\Entry;
use Redis;

class RedisRedrivableQueue implements RedrivableQueueInterface
{
    private String $queueKey;

    private String $redriveQueueKey;

    private Redis $redis;

    public function __construct(String $queuPrefix, Redis $redis) {
        $this->queueKey = "$queuPrefix:queue";
        $this->redriveQueueKey = "$queuPrefix:redrive";
        $this->redis = $redis;
    }

    function enqueue(EntryInterface $entry, bool $replace = true): mixed
    {
        return $this->redis->lpush($this->queueKey, json_encode($entry));
    }

    public function dequeue(int $count = 1): array
    {
        $this->redis->watch($this->queueKey);
        $entry = null;
        if (count($this->redis->lRange($this->queueKey, 0, 0)) > 0) {
            $value = json_decode($this->redis->rPopLPush($this->queueKey, $this->redriveQueueKey));

            $entry = Entry::builder()->key($value->key)->value($value->value)->build();
        }
        return $entry ? [$entry] : [];
    }

    public function redrive()
    {
        foreach ($this->redis->lRange($this->redriveQueueKey, 0, -1) as $key) {
            $this->redis->rPopLPush($this->redriveQueueKey, $this->queueKey);
        }
    }

    public function commit(EntryInterface $entry): mixed
    {
        return $this->redis->lrem($this->redriveQueueKey, json_encode($entry), 0);
    }
}