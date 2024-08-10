<?php

namespace Amtgard\SetQueue\DataStructure\Impl\InMemory;

use Amtgard\SetQueue\DataStructure\DataStructureConfig;
use Amtgard\SetQueue\DataStructure\HashSetFactory;

class InMemoryHashSetFactory implements HashSetFactory
{
    public function create(DataStructureConfig $config, string $name)
    {
        return new InMemoryHashSet();
    }
}