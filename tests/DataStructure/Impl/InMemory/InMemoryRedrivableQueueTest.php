<?php

namespace DataStructure\Impl\InMemory;

use Amtgard\SetQueue\DataStructure\Impl\InMemory\InMemoryRedrivableQueue;
use PHPUnit\Framework\TestCase;
use function PHPUnit\Framework\assertEquals;
use function PHPUnit\Framework\assertNull;

class InMemoryRedrivableQueueTest extends TestCase
{

    public function testQueueFifo() {
        $queue = new InMemoryRedrivableQueue();
        $queue->enqueue("ENTRY1");
        $queue->enqueue("ENTRY2");
        $queue->enqueue("ENTRY3");
        assertEquals(["ENTRY1"], $queue->dequeue());
        assertEquals(["ENTRY2"], $queue->dequeue());
        assertEquals(["ENTRY3"], $queue->dequeue());
    }

    public function testRedriveRequeues()
    {
        $queue = new InMemoryRedrivableQueue();
        $queue->enqueue("ENTRY1");
        assertEquals(["ENTRY1"], $queue->dequeue());
        assertNull($queue->dequeue()[0]);
        $queue->redrive();
        assertEquals(["ENTRY1"], $queue->dequeue());
    }

    public function testWhenCommit_thenNotRequeued() {
        $queue = new InMemoryRedrivableQueue();
        $queue->enqueue("ENTRY1");
        assertEquals(["ENTRY1"], $queue->dequeue());
        assertNull($queue->dequeue()[0]);
        $queue->commit("ENTRY1");
        $queue->redrive();
        assertNull($queue->dequeue()[0]);
    }
}