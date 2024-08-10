<?php

namespace Amtgard\SetQueue\DataStructure\Impl;

use Amtgard\SetQueue\DataStructure\RedrivableQueue;
use Cassandra\Set;

class InMemoryRedrivableQueue implements RedrivableQueue
{
    private array $queue = [];

    private Set $redrive;

    public function __construct() {
        $this->redrive = new Set();
    }

    public function enqueue(string $entry)
    {
        array_push($this->queue, $entry);
    }

    public function dequeue(): string
    {
        $entry = array_shift($this->queue);
        $this->redrive->add($entry);
        return $entry;
    }

    public function commit(string $entry)
    {
        $this->redrive->remove($entry);
    }

    public function redrive()
    {
        foreach ($this->redrive->values() as $entry) {
            $this->enqueue($entry);
        }
    }
}