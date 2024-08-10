<?php

namespace Amtgard\SetQueue\DataStructure\Impl\InMemory;

use Amtgard\SetQueue\DataStructure\RedrivableQueue;

class InMemoryRedrivableQueue implements RedrivableQueue
{
    private array $queue = [];

    private array $redrive = [];

    public function enqueue(string $entry)
    {
        array_push($this->queue, $entry);
    }

    public function dequeue(int $count = 1): array
    {
        if (count($this->queue) > 0) {
            $entry = array_shift($this->queue);
            $this->redrive[$entry] = $entry;
            return [$entry];
        } else {
            return [];
        }
    }

    public function commit(string $entry)
    {
        unset($this->redrive[$entry]);
    }

    public function redrive()
    {
        foreach ($this->redrive as $entry) {
            $this->enqueue($entry);
        }
    }
}