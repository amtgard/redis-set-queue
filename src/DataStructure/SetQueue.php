<?php

namespace Amtgard\SetQueue\DataStructure;

use Amtgard\Interface\EntryInterface;
use Amtgard\Interface\HashSetInterface;
use Amtgard\Interface\RedrivableQueueInterface;
use Amtgard\Interface\SetQueueInterface;
use Optional\Optional;

class SetQueue implements SetQueueInterface
{
    private HashSetInterface $set;
    private RedrivableQueueInterface $queue;
    private String $name;
    private DataStructureConfig $config;
    public function __construct(String $name, DataStructureConfig $config, HashSetFactoryInterface $setFactory, RedrivableQueueFactoryInterface $queueFactory) {
        $this->name = $name;
        $this->config = $config;
        $this->set = $setFactory->create($this->config, $this->name);
        $this->queue = $queueFactory->create($this->config, $this->name);
    }

    public function getName(): String
    {
        return $this->name;

    }

    public function enqueue(EntryInterface $entry, bool $replace = true): mixed {
        if ($this->set->contains($entry)) {
            if ($replace) {
                $this->set->add($entry);
            }
            return $this->set->get($entry);
        } else {
            $this->queue->enqueue($entry);
            $this->set->add($entry);
            return $this->set->get($entry);
        }
    }

    public function dequeue($count = 1): array {
        $entries = $this->queue->dequeue($count);
        $values = $this->set->getList($entries);
        $dequeues = [];
        foreach ($values as $index => $value) {
            $entry = $entries[$index];
            Optional::ofNullable($value)
                ->ifPresent(function() use ($entry, &$dequeues) {
                    $dequeues[] = $entry;
                });
        }
        return $dequeues;
    }

    public function commit(EntryInterface $entry): mixed {
        $this->set->remove($entry);
        return $this->queue->commit($entry);
    }

    public function redrive() {
        $this->queue->redrive();
    }

}