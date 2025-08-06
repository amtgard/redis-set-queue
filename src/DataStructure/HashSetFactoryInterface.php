<?php

namespace Amtgard\SetQueue\DataStructure;

interface HashSetFactoryInterface
{
    public function create(DataStructureConfig $config, String $name);
}