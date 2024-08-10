<?php

namespace Amtgard\SetQueue\DataStructure;

interface DataStructureConfig
{
    public function setConfig(array|String $config, mixed $value = null);
    public function getConfig(): array;
}