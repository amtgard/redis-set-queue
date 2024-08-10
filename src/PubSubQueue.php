<?php

namespace Amtgard\SetQueue;

use Amtgard\SetQueue\DataStructure\DataStructureConfig;
use Amtgard\SetQueue\DataStructure\Entry;
use Amtgard\SetQueue\DataStructure\Impl\DefaultEntry;
use Amtgard\SetQueue\DataStructure\SetQueue;
use Optional\Optional;

/**
 * Flow
 *  On startup, messages in the redrive Q are moved to the principal Q
 *  During operation, messages are sent via send() to the Q
 *      When read, they are temporarily enqueued to the redrive Q for disaster recovery
 *      On success, the entry is commit()-ed, which removes it from the redrive Q
 *      On failure, a consumer error handler is called for recovery, and the message is commit()-ed, to remove it from the redrive Q
 */
class PubSubQueue
{

    public static String $SUBSCRIBER_EMPTY_ERROR = "Subscriber must not be empty";
    public static String $SUBSCRIBER_NAME_INVALID_ERROR = "Subscriber does not exist";
    public static String $QUEUE_NAME_INVALID_ERROR = "Queue does not exist";

    /**
     * @var SetQueue[]
     */
    private array $Q;

    /**
     * @var callable[]
     */
    private array $subscriptions;

    /**
     * @var callable
     */
    private array $subscriberFailureHandlers;

    public function __construct() {
        $this->Q = [];
        $this->subscriptions = [];
        $this->queueFailureHandlers = [];
    }

    public function addQueue(SetQueue $setQueue): String {
        if (!array_key_exists($setQueue->getName(), $this->Q)) {
            $this->Q[$setQueue->getName()] = $setQueue;
        }
        return $setQueue->getName();
    }

    public function redrive($queueName) {
        if (!isset($this->Q[$queueName])) {
            throw new \InvalidArgumentException(PubSubQueue::$QUEUE_NAME_INVALID_ERROR);
        }
        $this->Q[$queueName]->redrive();
    }

    public function subscribe(String $queueName, callable $callback, callable $failure = null): String {
        if (!isset($this->Q[$queueName])) {
            throw new \InvalidArgumentException(PubSubQueue::$QUEUE_NAME_INVALID_ERROR);
        }
        $this->subscriptions[$queueName] = $callback;
        if (is_callable($failure)) {
            $this->onConsumeFailure($queueName, $failure);
        }
        return $queueName;
    }

    public function pump(String $queueName, $count = 1) {
        $entries = $this->pull($queueName, $count);
        foreach ($entries as $entry) {
            Optional::ofNullable($entry)
                ->ifPresent(function() use ($queueName, $entry) {
                    $this->callSubscribers($queueName, $entry);
                });
        }
    }

    private function onConsumeFailure(String $queueName, callable $callback) {
        $this->subscriberFailureHandlers[$queueName] = $callback;
    }

    public function unsubscribe(String $queueName) {
        if (!isset($this->subscriptions[$queueName])) {
            throw new \InvalidArgumentException(PubSubQueue::$QUEUE_NAME_INVALID_ERROR);
        }
        unset($this->subscriptions[$queueName]);
    }

    public function send(String $queueName, String $key, String $message, bool $replace = true): String {
        if (!isset($this->Q[$queueName])) {
            throw new \Exception("Queue is not available");
        }
        return $this->Q[$queueName]->enqueue($key, $message);
    }

    private function callSubscribers(String $queueName, Entry $entry) {
        if (!isset($this->Q[$queueName])) {
            throw new \Exception("Queue is not available");
        }
        $callback = $this->subscriptions[$queueName];
        Optional::ofNullable($callback)
            ->ifPresent(function() use ($queueName, $callback, $entry) {
                try {
                    call_user_func($callback, $entry->getKey(), $entry->getMessage());
                    $this->Q[$queueName]->commit($entry->getKey());
                } catch (\Exception $e) {
                    // rollback somehow
                    $this->callErrorHandlers($queueName, $e, $entry);
                }
            });
    }

    private function callErrorHandlers(String $queueName, \Exception $e, Entry $entry) {
        if (isset($this->subscriberFailureHandlers[$queueName])) {
            try {
                call_user_func($this->subscriberFailureHandlers[$queueName], $e, $entry->getKey(), $entry->getMessage());
            } catch (\Exception $e) {
                // Well, we tried ...
            }
        }
        $this->Q[$queueName]->commit($entry->getKey());
    }

    private function pull(String $queueName, $count = 1): ?array {
        if (isset($this->Q[$queueName])) {
            return $this->Q[$queueName]->dequeue($count);
        }
        return null;
    }
}