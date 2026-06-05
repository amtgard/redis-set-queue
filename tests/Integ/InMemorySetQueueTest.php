<?php

namespace Integ;

use Amtgard\SetQueue\DataStructure\DataStructureConfig;
use Amtgard\SetQueue\DataStructure\Entry;
use Amtgard\SetQueue\DataStructure\HashSetFactoryInterface;
use Amtgard\SetQueue\DataStructure\Impl\InMemory\InMemoryHashSetFactory;
use Amtgard\SetQueue\DataStructure\Impl\InMemory\InMemoryRedrivableQueueFactory;
use Amtgard\SetQueue\DataStructure\RedrivableQueueFactoryInterface;
use Amtgard\SetQueue\DataStructure\SetQueue;
use Phake;
use PHPUnit\Framework\TestCase;

class InMemorySetQueueTest extends TestCase
{
    private SetQueue $queue;
    private HashSetFactoryInterface $hashSetFactory;
    private RedrivableQueueFactoryInterface $redrivableQueueFactory;

    protected function setUp(): void
    {
        parent::setUp();
        $config = Phake::mock(DataStructureConfig::class);
        $this->hashSetFactory = new InMemoryHashSetFactory();
        $this->redrivableQueueFactory = new InMemoryRedrivableQueueFactory();
        $this->queue = new SetQueue("SETQUEUE_NAME", $config, $this->hashSetFactory, $this->redrivableQueueFactory);
    }

    public function testEnqueueDeque() {
        $entry = Entry::builder()->key("KEY")->value("VALUE")->build();
        $this->queue->enqueue($entry);
        self::assertEquals([$entry], $this->queue->dequeue());
    }

    public function testRedriveRequeues() {
        $entry = Entry::builder()->key("KEY")->value("VALUE")->build();
        $this->queue->enqueue($entry);
        self::assertEquals([$entry], $this->queue->dequeue());
        $this->queue->redrive();
        self::assertEquals([$entry], $this->queue->dequeue());
    }
    public function testCommitClearsQueue() {
        $entry = Entry::builder()->key("KEY")->value("VALUE")->build();
        $this->queue->enqueue($entry);
        self::assertEquals([$entry], $this->queue->dequeue());
        $this->queue->commit($entry);
        self::assertEquals([], $this->queue->dequeue());
    }

}