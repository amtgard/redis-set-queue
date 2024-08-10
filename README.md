# redis-set-queue
A setqueue built on top of redis

## Usage

This library is designed to be used with a cron or worker library, such as [workerman](https://github.com/walkor/workerman) (`composer require workerman/workerman`).

It operates as a general pub/sub queue with the following trick: if any key is already in the queue, it will not be re-queued. 
When a duplicate key is requeued, the default operation is to return the existing message without replacement.

`send()` with the optional $replace = true parameter will replace the existing message with the new message.

In either case, no more than a single message will ever exist for the same key at the same time.

##### [NOTE: Key idempotency is best-effort. There are conditions in which messages are evicted or duplicated.]

General use is enshrined in code in the `RedisPubSubQueueTest.php` test file, but in general use is expected from two systems:
1. Publishers
2. Subscribers

### Subscriber Setup
```php
$config = new RedisDataStructureConfig();
$config->setConfig([
    'host' => '127.0.0.1',
    'port' => 36379,
]);
$redis = new Redis();
$redis->pconnect($config->getConfig()['host'], $config->getConfig()['port']);

$publisherName = "TEST";
$readDelay = 100; // microseconds

if ($redis->isConnected()) {
    $hashSetFactory = new RedisHashSetFactory();
    $redrivableQueueFactory = new RedisRedrivableQueueFactory();
    $queue = new SetQueue($publisherName, $config, $hashSetFactory, $redrivableQueueFactory);
    
    $pubSub = new PubSubQueue();
    $pubSub->addQueue($queue);
    $pubSub->redrive($queue->getName());
    
    $callCount = 0;
    $handle = $pubSub->subscribe($queue->getName(), function($key, $message) use (&$callCount) {
        if ($message == "MESSAGE1") {
            $callCount++;
        }
    });
        
    do {
        $pubSub->pump($handle);
        // Run every 100 milliseconds
        usleep($readDelay * 1000);
    } while (true);
}
```

### Publisher Setup
```php
$config = new RedisDataStructureConfig();
$config->setConfig([
    'host' => '127.0.0.1',
    'port' => 36379,
]);
$redis = new Redis();
$redis->pconnect($config->getConfig()['host'], $config->getConfig()['port']);

$publisherName = "TEST";

if ($redis->isConnected()) {
    $hashSetFactory = new RedisHashSetFactory();
    $redrivableQueueFactory = new RedisRedrivableQueueFactory();
    $queue = new SetQueue($publisherName, $config, $hashSetFactory, $redrivableQueueFactory);
    
    $pubSub = new PubSubQueue();
    $handle = $pubSub->addQueue($queue);

    $pubSub->send($handle, "KEY1", "MESSAGE1");
}

```
