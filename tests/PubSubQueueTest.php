<?php

use Amtgard\SetQueue\DataStructure\Entry;
use Amtgard\SetQueue\PubSubQueue;
use function PHPUnit\Framework\assertEquals;

class PubSubQueueTest extends \PHPUnit\Framework\TestCase
{
    public function testAddQueueReturnsQueueName() {
        $setQ = Phake::mock(Amtgard\SetQueue\DataStructure\SetQueue::class);
        Phake::when($setQ)->redrive();
        Phake::when($setQ)->getName()->thenReturn('test');
        $queue = new PubSubQueue();
        \PHPUnit\Framework\assertEquals("test", $queue->addQueue($setQ));
        $queue->redrive("test");
        Phake::verify($setQ)->redrive();
    }

    public function testWhenSubscribeWithNoMessage_thenReturnHandle() {
        $setQ = Phake::mock(Amtgard\SetQueue\DataStructure\SetQueue::class);
        $entry = Phake::mock(Entry::class);
        Phake::when($entry)->hasMessage()->thenReturn(false);
        Phake::when($setQ)->redrive();
        Phake::when($setQ)->getName()->thenReturn('test');
        Phake::when($setQ)->dequeue()->thenReturn($entry);

        $queue = new PubSubQueue();
        $queue->addQueue($setQ);

        self::assertEquals("test",
            $queue->subscribe("test", function($key, $message) {}));
    }

    public function testWhenSubscribe_thenCall() {
        $setQ = Phake::mock(Amtgard\SetQueue\DataStructure\SetQueue::class);
        $entry = Phake::mock(Entry::class);
        Phake::when($entry)->getKey()->thenReturn('key');
        Phake::when($entry)->getMessage()->thenReturn('value');
        Phake::when($entry)->hasMessage()->thenReturn(true);
        Phake::when($setQ)->redrive();
        Phake::when($setQ)->getName()->thenReturn('test');
        Phake::when($setQ)->dequeue(1)->thenReturn([$entry]);

        $queue = new PubSubQueue();
        $queue->addQueue($setQ);

        self::assertEquals(
            "test",
            $queue->subscribe("test",
                function($key, $message) use ($entry) {
                    assertEquals("key", $key);
                    assertEquals("value", $message);
                }));

        $queue->pump("test");

        Phake::verify($setQ)->commit($entry->getKey());
    }

    public function testWhenCallThrows_thenFailureHandlerCalled() {
        $setQ = Phake::mock(Amtgard\SetQueue\DataStructure\SetQueue::class);
        $entry = Phake::mock(Entry::class);
        Phake::when($entry)->getKey()->thenReturn('key');
        Phake::when($entry)->getMessage()->thenReturn('value');
        Phake::when($entry)->hasMessage()->thenReturn(true);
        Phake::when($setQ)->redrive();
        Phake::when($setQ)->getName()->thenReturn('test');
        Phake::when($setQ)->dequeue(1)->thenReturn([$entry]);

        $queue = new PubSubQueue();
        $queue->addQueue($setQ);

        $failureCount = 0;
        self::assertEquals(
            "test",
            $queue->subscribe("test",
                function($key, $message) use ($entry) {
                    throw new \Exception("Ruckus!");
                }, function(\Exception $e, $key, $message) use ($entry, &$failureCount) {
                    $failureCount++;
                }));

        $queue->pump("test");

        assertEquals(1, $failureCount);
        Phake::verify($setQ)->commit($entry->getKey());
    }

    public function testWhenInvalidQueueName_thenThrowsException() {
        $queue = new PubSubQueue();
        $this->expectException(\InvalidArgumentException::class);

        $this->expectExceptionMessage(PubSubQueue::$QUEUE_NAME_INVALID_ERROR);
        $queue->subscribe("test", function($key, $message) {});
    }

    public function testWhenUnsubscribed_thenSubsequentCallNotMade() {
        $setQ = Phake::mock(Amtgard\SetQueue\DataStructure\SetQueue::class);
        $entry = Phake::mock(\Amtgard\SetQueue\DataStructure\Entry::class);
        Phake::when($entry)->getKey()->thenReturn('key');
        Phake::when($entry)->getMessage()->thenReturn('value');
        Phake::when($entry)->hasMessage()->thenReturn(true);
        Phake::when($setQ)->redrive();
        Phake::when($setQ)->getName()->thenReturn('test');
        Phake::when($setQ)->dequeue(1)->thenReturn([$entry]);

        $queue = new PubSubQueue();
        $queue->addQueue($setQ);

        $subscriber1Count = 0;
        $handle1 = $queue->subscribe("test",
            function($key, $message) use (&$subscriber1Count) {
                $subscriber1Count++;
            });
        $queue->pump("test");

        $queue->unsubscribe($handle1);

        $queue->pump("test");

        assertEquals(1, $subscriber1Count);
    }

    public function testWhenInvalidQueue_thenUnsubscribeThrows() {
        $setQ = Phake::mock(Amtgard\SetQueue\DataStructure\SetQueue::class);
        Phake::when($setQ)->redrive();
        Phake::when($setQ)->getName()->thenReturn('test1');

        $queue = new PubSubQueue();
        $queue->addQueue($setQ);

        $this->expectException(\InvalidArgumentException::class);

        $this->expectExceptionMessage(PubSubQueue::$QUEUE_NAME_INVALID_ERROR);
        $queue->unsubscribe("test2");
    }

    public function testSendMessage() {
        $setQ = Phake::mock(Amtgard\SetQueue\DataStructure\SetQueue::class);
        Phake::when($setQ)->redrive();
        Phake::when($setQ)->getName()->thenReturn('test');
        Phake::when($setQ)->enqueue("KEY1", "VALUE1")->thenReturn("VALUE1");

        $queue = new PubSubQueue();
        $queue->addQueue($setQ);

        $queue->send("test", "KEY1", "VALUE1");
        Phake::verify($setQ)->enqueue("KEY1", "VALUE1");
    }

}