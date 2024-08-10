<?php

namespace Amtgard\SetQueue\DataStructure\Impl;

use Amtgard\SetQueue\DataStructure\DataStructureConfig;
use Amtgard\SetQueue\DataStructure\HashSetFactory;

class InMemoryHashSetFactory implements HashSetFactory
{

    public function __construct(DataStructureConfig $config)
    {
    }

    public function create(string $name)
    {
        return new InMemoryHashSet();
    }
}