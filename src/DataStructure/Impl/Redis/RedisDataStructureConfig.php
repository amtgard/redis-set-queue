<?php

namespace Amtgard\SetQueue\DataStructure\Impl\Redis;

use Amtgard\SetQueue\DataStructure\DataStructureConfig;
use http\Exception\InvalidArgumentException;

class RedisDataStructureConfig implements DataStructureConfig
{

    private array $config;

    public function setConfig(array|String $config, mixed $value = null)
    {
        if (is_array($config)) {
            if (!isset($config['host'])) throw new InvalidArgumentException('Redis config key "host" is required.');
            if (!isset($config['port'])) throw new InvalidArgumentException('Redis config key "port" is required.');
            $this->config = $config;
        } else {
            $this->config[$config] = $value;
        }
    }

    public function getConfig(): array
    {
        return $this->config;
    }
}