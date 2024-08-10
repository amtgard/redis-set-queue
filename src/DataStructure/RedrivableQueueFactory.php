<?php

namespace Amtgard\SetQueue\DataStructure;

interface QueueFactory
{
    public function __construct(DataStructureConfig $config);
    public function create(String $name);
}