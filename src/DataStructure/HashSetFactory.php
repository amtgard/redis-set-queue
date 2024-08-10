<?php

namespace Amtgard\SetQueue;

interface HashSetFactory
{
    public function __construct(DataStructureConfig $config);
    public function create(String $name);
}