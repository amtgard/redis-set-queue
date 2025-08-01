<?php

namespace Amtgard\SetQueue\DataStructure\Impl\InMemory;

use Amtgard\SetQueue\DataStructure\DataStructureConfig;
use Amtgard\SetQueue\DataStructure\HashSetFactoryInterface;

class InMemoryHashSetFactory implements HashSetFactoryInterface
{
    public function create(DataStructureConfig $config, string $name)
    {
        return new InMemoryHashSet();
    }
}