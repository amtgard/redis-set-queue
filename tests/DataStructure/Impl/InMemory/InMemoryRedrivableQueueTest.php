<?php

namespace DataStructure\Impl\InMemory;

use Amtgard\SetQueue\DataStructure\Entry;
use Amtgard\SetQueue\DataStructure\Impl\InMemory\InMemoryRedrivableQueue;
use PHPUnit\Framework\TestCase;
use function PHPUnit\Framework\assertEquals;
use function PHPUnit\Framework\assertNull;

class InMemoryRedrivableQueueTest extends TestCase
{

    public function testQueueFifo() {
        $queue = new InMemoryRedrivableQueue();
        $entry1 = Entry::builder()->value("ENTRY1")->build();
        $entry2 = Entry::builder()->value("ENTRY2")->build();
        $entry3 = Entry::builder()->value("ENTRY3")->build();
        $queue->enqueue($entry1);
        $queue->enqueue($entry2);
        $queue->enqueue($entry3);
        assertEquals([$entry1], $queue->dequeue());
        assertEquals([$entry2], $queue->dequeue());
        assertEquals([$entry3], $queue->dequeue());
    }

    public function testRedriveRequeues()
    {
        $queue = new InMemoryRedrivableQueue();
        $entry1 = Entry::builder()->value("ENTRY1")->build();
        $queue->enqueue($entry1);
        assertEquals([$entry1], $queue->dequeue());
        assertNull($queue->dequeue()[0]);
        $queue->redrive();
        assertEquals([$entry1], $queue->dequeue());
    }

    public function testWhenCommit_thenNotRequeued() {
        $queue = new InMemoryRedrivableQueue();

        $entry1 = Entry::builder()->value("ENTRY1")->build();
        $queue->enqueue($entry1);
        assertEquals([$entry1], $queue->dequeue());
        assertNull($queue->dequeue()[0]);
        $queue->commit($entry1);
        $queue->redrive();
        assertNull($queue->dequeue()[0]);
    }
}