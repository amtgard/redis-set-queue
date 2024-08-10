<?php

namespace Amtgard\SetQueue;

interface DataStructureConfig
{
    public function setConfig(array $config);
    public function getConfig(): array;
}