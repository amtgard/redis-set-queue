<?php

namespace Amtgard\SetQueue\DataStructure\Impl\InMemory;

use Amtgard\Interface\EntryInterface;
use Amtgard\Interface\RedrivableQueueInterface;

class InMemoryRedrivableQueue implements RedrivableQueueInterface
{
    private array $queue = [];

    private array $redrive = [];

    function enqueue(EntryInterface $entry, bool $replace = true): mixed
    {
        return array_push($this->queue, $entry);
    }

    public function dequeue(int $count = 1): array
    {
        if (count($this->queue) > 0) {
            $entry = array_shift($this->queue);
            $this->redrive[$entry->getHash()] = $entry;
            return [$entry];
        } else {
            return [];
        }
    }

    public function redrive()
    {
        foreach ($this->redrive as $entry) {
            $this->enqueue($entry);
        }
    }

    public function commit(EntryInterface $entry): mixed
    {
        unset($this->redrive[$entry->getHash()]);
        return true;
    }
}