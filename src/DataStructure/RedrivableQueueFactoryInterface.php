<?php

namespace Amtgard\SetQueue\DataStructure;

interface RedrivableQueueFactoryInterface
{
    public function create(DataStructureConfig $config, String $name);
}