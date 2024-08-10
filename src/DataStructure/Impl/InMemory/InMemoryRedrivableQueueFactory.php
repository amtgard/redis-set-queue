<?php

namespace Amtgard\SetQueue\DataStructure\Impl\InMemory;

use Amtgard\SetQueue\DataStructure\DataStructureConfig;
use Amtgard\SetQueue\DataStructure\RedrivableQueueFactory;

class InMemoryRedrivableQueueFactory implements RedrivableQueueFactory
{
    public function create(DataStructureConfig $config, string $name)
    {
        return new InMemoryRedrivableQueue();
    }
}