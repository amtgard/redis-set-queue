<?php

namespace Amtgard\SetQueue\DataStructure;

interface HashSetFactory
{
    public function create(DataStructureConfig $config, String $name);
}