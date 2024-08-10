<?php

namespace Amtgard\SetQueue\DataStructure;

use Amtgard\SetQueue\DataStructure\Impl\DefaultEntry;
use Optional\Optional;

class SetQueue
{
    private HashSet $set;
    private RedrivableQueue $queue;
    private String $name;
    private DataStructureConfig $config;
    public function __construct(String $name, DataStructureConfig $config, HashSetFactory $setFactory, RedrivableQueueFactory $queueFactory) {
        $this->name = $name;
        $this->config = $config;
        $this->set = $setFactory->create($this->config, $this->name);
        $this->queue = $queueFactory->create($this->config, $this->name);
    }

    public function getName(): String {
        return $this->name;
    }

    public function enqueue(String $key, mixed $message, bool $replace = true) {
        $entry = new Entry($key);
        $entry->setMessage($message);
        if ($this->set->contains($key)) {
            if ($replace) {
                $this->set->add($entry);
            }
            return $this->set->get($key);
        } else {
            $this->queue->enqueue($key);
            $this->set->add($entry);
            return $this->set->get($key);
        }
    }

    public function dequeue($count = 1): array {
        $keys = $this->queue->dequeue($count);
        $values = $this->set->getList($keys);
        $entries = [];
        foreach ($values as $index => $value) {
            $key = $keys[$index];
            Optional::ofNullable($value)
                ->ifPresent(function() use ($key, &$entries) {
                    $entry = new Entry($key);
                    $entry->setMessage($this->set->get($entry->getKey()));
                    $entries[] = $entry;
                });
        }
        return $entries;
    }

    public function commit(String $key) {
        $this->set->remove($key);
        return $this->queue->commit($key);
    }

    public function redrive() {
        $this->queue->redrive();
    }

}