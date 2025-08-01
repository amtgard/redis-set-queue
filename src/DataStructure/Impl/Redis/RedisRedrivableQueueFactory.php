<?php

namespace Amtgard\SetQueue\DataStructure\Impl\Redis;

use Amtgard\SetQueue\DataStructure\DataStructureConfig;
use Amtgard\SetQueue\DataStructure\RedrivableQueueFactoryInterface;
use Redis;

class RedisRedrivableQueueFactory implements RedrivableQueueFactoryInterface
{

    public function create(DataStructureConfig $config, string $name)
    {
        $rc = $config->getConfig();
        $redis = new Redis();
        $redis->connect($rc['host'], $rc['port']);
        return new RedisRedrivableQueue($name, $redis);
    }
}