<?php

namespace Amtgard\SetQueue\DataStructure\Impl\Redis;

use Amtgard\SetQueue\DataStructure\DataStructureConfig;
use Amtgard\SetQueue\DataStructure\HashSetFactoryInterface;
use Redis;

class RedisHashSetFactory implements HashSetFactoryInterface
{
    public function create(DataStructureConfig $config, string $name)
    {
        $rc = $config->getConfig();
        $redis = new Redis();
        $redis->connect($rc['host'], $rc['port']);
        return new RedisHashSet($name, $redis);
    }
}