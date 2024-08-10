<?php

namespace Integ;

use Amtgard\SetQueue\DataStructure\DataStructureConfig;
use Amtgard\SetQueue\DataStructure\Entry;
use Amtgard\SetQueue\DataStructure\HashSetFactory;
use Amtgard\SetQueue\DataStructure\Impl\InMemory\InMemoryHashSetFactory;
use Amtgard\SetQueue\DataStructure\Impl\InMemory\InMemoryRedrivableQueueFactory;
use Amtgard\SetQueue\DataStructure\RedrivableQueueFactory;
use Amtgard\SetQueue\DataStructure\SetQueue;
use Phake;
use PHPUnit\Framework\TestCase;

class InMemorySetQueueTest extends TestCase
{
    private SetQueue $queue;
    private HashSetFactory $hashSetFactory;
    private RedrivableQueueFactory $redrivableQueueFactory;

    protected function setUp(): void
    {
        parent::setUp();
        $config = Phake::mock(DataStructureConfig::class);
        $this->hashSetFactory = new InMemoryHashSetFactory();
        $this->redrivableQueueFactory = new InMemoryRedrivableQueueFactory();
        $this->queue = new SetQueue("SETQUEUE_NAME", $config, $this->hashSetFactory, $this->redrivableQueueFactory);
    }

    public function testEnqueueDeque() {
        $this->queue->enqueue("KEY", "VALUE");
        $entry = new Entry("KEY");
        $entry->setMessage("VALUE");
        self::assertEquals([$entry], $this->queue->dequeue());
    }

    public function testRedriveRequeues() {
        $this->queue->enqueue("KEY", "VALUE");
        $entry = new Entry("KEY");
        $entry->setMessage("VALUE");
        self::assertEquals([$entry], $this->queue->dequeue());
        $this->queue->redrive();
        self::assertEquals([$entry], $this->queue->dequeue());
    }
    public function testCommitClearsQueue() {
        $this->queue->enqueue("KEY", "VALUE");
        $entry = new Entry("KEY");
        $entry->setMessage("VALUE");
        self::assertEquals([$entry], $this->queue->dequeue());
        $this->queue->commit("KEY");
        self::assertEquals(null, $this->queue->dequeue()[0]);
    }

}