<?php

namespace Amtgard\SetQueue\DataStructure;

interface RedrivableQueueFactory
{
    public function create(DataStructureConfig $config, String $name);
}