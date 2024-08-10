<?php

namespace Amtgard\SetQueue\DataStructure\Impl\Redis;

use Amtgard\SetQueue\DataStructure\DataStructureConfig;
use Amtgard\SetQueue\DataStructure\HashSetFactory;
use Redis;

class RedisHashSetFactory implements HashSetFactory
{
    public function create(DataStructureConfig $config, string $name)
    {
        $rc = $config->getConfig();
        $redis = new Redis();
        $redis->connect($rc['host'], $rc['port']);
        return new RedisHashSet($name, $redis);
    }
}