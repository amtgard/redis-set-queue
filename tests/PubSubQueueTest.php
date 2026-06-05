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
        \PHPUnit\Framework\assertEquals("test", $queue->addQueue($setQ->getName(), $setQ));
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
        $queue->addQueue($setQ->getName(), $setQ);

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
        $queue->addQueue("test", $setQ);

        self::assertEquals(
            "test",
            $queue->subscribe("test",
                function($key, $message) use ($entry) {
                    assertEquals("key", $key);
                    assertEquals("value", $message);
                }));

        $queue->callConsumers("test");

        Phake::verify($setQ)->commit($entry);
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
        $queue->addQueue("test", $setQ);

        $failureCount = 0;
        self::assertEquals(
            "test",
            $queue->subscribe("test",
                function($key, $message) use ($entry) {
                    throw new \Exception("Ruckus!");
                }, function(\Exception $e, $key, $message) use ($entry, &$failureCount) {
                    $failureCount++;
                }));

        $queue->callConsumers("test");

        assertEquals(1, $failureCount);
        Phake::verify($setQ)->commit($entry);
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
        $queue->addQueue("test", $setQ);

        $subscriber1Count = 0;
        $handle1 = $queue->subscribe("test",
            function($key, $message) use (&$subscriber1Count) {
                $subscriber1Count++;
            });
        $queue->callConsumers("test");

        $queue->unsubscribe($handle1);

        $queue->callConsumers("test");

        assertEquals(1, $subscriber1Count);
    }

    public function testWhenInvalidQueue_thenUnsubscribeThrows() {
        $setQ = Phake::mock(Amtgard\SetQueue\DataStructure\SetQueue::class);
        Phake::when($setQ)->redrive();
        Phake::when($setQ)->getName()->thenReturn('test1');

        $queue = new PubSubQueue();
        $queue->addQueue("test", $setQ);

        $this->expectException(\InvalidArgumentException::class);

        $this->expectExceptionMessage(PubSubQueue::$QUEUE_NAME_INVALID_ERROR);
        $queue->unsubscribe("test2");
    }

    public function testSendMessage() {
        $setQ = Phake::mock(Amtgard\SetQueue\DataStructure\SetQueue::class);
        $entry = Entry::builder()->key("KEY1")->value("VALUE1")->build();
        Phake::when($setQ)->redrive();
        Phake::when($setQ)->getName()->thenReturn('test');
        Phake::when($setQ)->enqueue($entry)->thenReturn("VALUE1");

        $queue = new PubSubQueue();
        $queue->addQueue("test", $setQ);

        $queue->publish("test", "KEY1", "VALUE1");
        Phake::verify($setQ)->enqueue($entry, true);
    }

    public function testAddQueueDoesNotOverwriteExisting() {
        $setQ1 = Phake::mock(Amtgard\SetQueue\DataStructure\SetQueue::class);
        $setQ2 = Phake::mock(Amtgard\SetQueue\DataStructure\SetQueue::class);
        $entry = Phake::mock(Entry::class);
        Phake::when($entry)->getHash()->thenReturn('key');
        Phake::when($entry)->getValue()->thenReturn('value');
        Phake::when($setQ1)->dequeue(1)->thenReturn([$entry]);

        $queue = new PubSubQueue();
        $queue->addQueue('test', $setQ1);
        $queue->addQueue('test', $setQ2);
        $queue->subscribe('test', function () {});

        $queue->callConsumers('test');
        Phake::verify($setQ1)->dequeue(1);
        Phake::verify($setQ2, Phake::never())->dequeue(Phake::anyParameters());
    }

    public function testRedriveInvalidQueueThrows() {
        $queue = new PubSubQueue();
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(PubSubQueue::$QUEUE_NAME_INVALID_ERROR);
        $queue->redrive('missing');
    }

    public function testPublishInvalidQueueThrows() {
        $queue = new PubSubQueue();
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Queue is not available');
        $queue->publish('missing', 'KEY', 'VALUE');
    }

    public function testWhenFailureHandlerThrows_thenEntryStillCommitted() {
        $setQ = Phake::mock(Amtgard\SetQueue\DataStructure\SetQueue::class);
        $entry = Phake::mock(Entry::class);
        Phake::when($entry)->getHash()->thenReturn('key');
        Phake::when($entry)->getValue()->thenReturn('value');
        Phake::when($setQ)->dequeue(1)->thenReturn([$entry]);

        $queue = new PubSubQueue();
        $queue->addQueue('test', $setQ);
        $queue->subscribe('test',
            function () { throw new \Exception('consumer failed'); },
            function () { throw new \Exception('handler failed'); }
        );

        $queue->callConsumers('test');
        Phake::verify($setQ)->commit($entry);
    }

    public function testCallConsumersWithUnknownQueueDoesNothing() {
        $queue = new PubSubQueue();
        $queue->callConsumers('missing');
        self::assertTrue(true);
    }

    public function testCallSubscribersThrowsWhenQueueMissing() {
        $queue = new PubSubQueue();
        $entry = Entry::builder()->key('KEY')->value('VALUE')->build();
        $method = new \ReflectionMethod(PubSubQueue::class, 'callSubscribers');
        $method->setAccessible(true);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Queue is not available');
        $method->invoke($queue, 'missing', $entry);
    }

    public function testOnConsumeFailureRegistersHandler() {
        $queue = new PubSubQueue();
        $setQ = Phake::mock(Amtgard\SetQueue\DataStructure\SetQueue::class);
        $queue->addQueue('test', $setQ);

        $method = new \ReflectionMethod(PubSubQueue::class, 'onConsumeFailure');
        $method->setAccessible(true);
        $method->invoke($queue, 'test', function () {});

        $entry = Phake::mock(Entry::class);
        Phake::when($entry)->getHash()->thenReturn('key');
        Phake::when($entry)->getValue()->thenReturn('value');
        Phake::when($setQ)->dequeue(1)->thenReturn([$entry]);

        $queue->subscribe('test', function () { throw new \Exception('fail'); });
        $queue->callConsumers('test');
        Phake::verify($setQ)->commit($entry);
    }

    public function testSubscribeWithFailureHandlerRegistersHandler() {
        $setQ = Phake::mock(Amtgard\SetQueue\DataStructure\SetQueue::class);
        $entry = Phake::mock(Entry::class);
        Phake::when($entry)->getHash()->thenReturn('key');
        Phake::when($entry)->getValue()->thenReturn('value');
        Phake::when($setQ)->dequeue(1)->thenReturn([$entry]);

        $queue = new PubSubQueue();
        $queue->addQueue('test', $setQ);

        $handled = false;
        $queue->subscribe('test',
            function () { throw new \Exception('fail'); },
            function () use (&$handled) { $handled = true; }
        );
        $queue->callConsumers('test');

        self::assertTrue($handled);
    }

    public function testWhenFailureWithoutHandler_thenEntryCommitted() {
        $setQ = Phake::mock(Amtgard\SetQueue\DataStructure\SetQueue::class);
        $entry = Phake::mock(Entry::class);
        Phake::when($entry)->getHash()->thenReturn('key');
        Phake::when($entry)->getValue()->thenReturn('value');
        Phake::when($setQ)->dequeue(1)->thenReturn([$entry]);

        $queue = new PubSubQueue();
        $queue->addQueue('test', $setQ);
        $queue->subscribe('test', function () { throw new \Exception('consumer failed'); });

        $queue->callConsumers('test');
        Phake::verify($setQ)->commit($entry);
    }

}