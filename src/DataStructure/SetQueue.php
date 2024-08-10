<?php

namespace Amtgard\SetQueue;

class SetQueue
{
    private HashSet $set;
    private Queue $queue;
    private String $name;

    public function __construct(String $name, HashSetFactory $setFactory, QueueFactory $queueFactory) {
        $this->name = $name;
        $this->set = $setFactory->create($this->name);
        $this->queue = $queueFactory->create($this->name);
    }

    public function getName(): String {
        return $this->name;
    }

    public function enqueue(String $key, String $message) {
        if (!$this->set->contains($key)) {
            $this->set->add($key, $message);
            return $message;
        } else {
            $this->queue->enqueue($key);
            return $this->set->get($key);
        }
    }

    public function dequeue(): String {
        return $this->set->remove($this->queue->dequeue());
    }

}