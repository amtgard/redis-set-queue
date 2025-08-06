<?php

namespace Amtgard\SetQueue\DataStructure\Impl\Redis;

use Amtgard\Interface\EntryInterface;
use Amtgard\Interface\HashSetInterface;
use Redis;

class RedisHashSet implements HashSetInterface
{
    private String $setKey;
    private Redis $redis;

    public function __construct(String $setPrefix, Redis $redis) {
        $this->setKey = "$setPrefix:set";
        $this->redis = $redis;
    }

    public function add(EntryInterface $entry): mixed
    {
        return $this->redis->hset($this->setKey, $entry->getHash(), json_encode($entry->getValue())) ? $entry->getValue() : null;
    }

    public function contains(EntryInterface $entry): bool
    {
        return (bool)$this->redis->hExists($this->setKey, $entry->getHash());
    }

    public function remove(EntryInterface $entry): mixed
    {
        $response = $this->redis->multi()->hget($this->setKey, $entry->getHash())->hdel($this->setKey, $entry->getHash())->exec();
        return is_array($response) && count($response) == 2 ? json_decode($response[0], false) : null;
    }

    public function get(EntryInterface $entry): mixed
    {
        $value = $this->redis->hget($this->setKey, $entry->getHash());
        return $value ? json_decode($value, false) : null;
    }

    public function getList(array $entries): array
    {
        $values = [];
        foreach ($entries as $entry) {
            $values[] = $this->get($entry);
        }
        return $values;
    }
}