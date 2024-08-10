<?php

namespace Amtgard\SetQueue\DataStructure;

interface RedrivableQueue
{
    public function enqueue(String $entry);

    public function dequeue(int $count = 0): array;

    public function redrive();

    public function commit(String $entry);

}