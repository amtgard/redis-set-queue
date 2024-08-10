<?php

namespace Amtgard\SetQueue\DataStructure\Impl\Redis;

use Amtgard\SetQueue\DataStructure\Entry;
use Amtgard\SetQueue\DataStructure\HashSet;
use Optional\Optional;
use Redis;

class RedisHashSet implements HashSet
{
    private String $setKey;
    private Redis $redis;

    public function __construct(String $setPrefix, Redis $redis) {
        $this->setKey = "$setPrefix:set";
        $this->redis = $redis;
    }

    public function add(Entry $entry): mixed
    {
        return $this->redis->hset($this->setKey, $entry->getKey(), json_encode($entry->getMessage())) ? $entry->getMessage() : null;
    }

    public function contains(mixed $key): bool
    {
        return (bool)$this->redis->hExists($this->setKey, $key);
    }

    public function remove(mixed $key): mixed
    {
        $response = $this->redis->multi()->hget($this->setKey, $key)->hdel($this->setKey, $key)->exec();
        return is_array($response) && count($response) == 2 ? json_decode($response[0], false) : null;
    }

    public function get(mixed $key): mixed
    {
        $value = $this->redis->hget($this->setKey, $key);
        return $value ? json_decode($value, false) : null;
    }

    public function getList(array $keys): array
    {
        $values = [];
        foreach ($keys as $key) {
            $values[] = $this->get($key);
        }
        return $values;
    }
}