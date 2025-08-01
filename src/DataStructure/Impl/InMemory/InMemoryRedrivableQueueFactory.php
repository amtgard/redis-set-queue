<?php

namespace Amtgard\SetQueue\DataStructure\Impl\InMemory;

use Amtgard\SetQueue\DataStructure\DataStructureConfig;
use Amtgard\SetQueue\DataStructure\RedrivableQueueFactoryInterface;

class InMemoryRedrivableQueueFactory implements RedrivableQueueFactoryInterface
{
    public function create(DataStructureConfig $config, string $name)
    {
        return new InMemoryRedrivableQueue();
    }
}