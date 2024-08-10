<?php

namespace Amtgard\SetQueue\DataStructure\Impl;

use Amtgard\SetQueue\DataStructure\DataStructureConfig;
use Amtgard\SetQueue\DataStructure\QueueFactory;

class InMemoryQueueFactory implements QueueFactory
{

    public function __construct(DataStructureConfig $config)
    {
    }

    public function create(string $name)
    {
        return new InMemoryRedrivableQueue();
    }
}