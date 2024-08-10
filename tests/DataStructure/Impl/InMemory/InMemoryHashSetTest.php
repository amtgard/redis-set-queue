<?php

declare(strict_types=1);

use Amtgard\SetQueue\DataStructure\Entry;
use Amtgard\SetQueue\DataStructure\Impl\InMemory\InMemoryHashSet;
use PHPUnit\Framework\TestCase;

class InMemoryHashSetTest extends TestCase {
    public function testWhenAddKey_ContainsKey() {
        $set = new InMemoryHashSet();
        $entry = new Entry("KEY");
        $entry->setMessage("VALUE");
        $set->add($entry);
        \PHPUnit\Framework\assertTrue($set->contains("KEY"));
    }

    public function testWhenValueRemoved_thenNotContained() {
        $entry1 = new Entry("KEY1");
        $entry1->setMessage("VALUE1");
        $entry2 = new Entry("KEY2");
        $entry2->setMessage("VALUE2");
        $set = new InMemoryHashSet();
        $set->add($entry1);
        $set->add($entry2);
        \PHPUnit\Framework\assertEquals("VALUE1", $set->remove("KEY1"));
        \PHPUnit\Framework\assertFalse($set->contains("KEY1"));
    }

    public function testWhenKeyNotAdded_thenNotContained() {
        $set = new InMemoryHashSet();
        \PHPUnit\Framework\assertFalse($set->contains("KEY"));
    }

    public function testWhenKeyIsAdded_thenGetReturnsValue() {
        $set = new InMemoryHashSet();
        $entry = new Entry("KEY");
        $entry->setMessage("VALUE");
        $set->add($entry);
        \PHPUnit\Framework\assertEquals("VALUE", $set->get("KEY"));
    }

    public function testWhenKeyIsRemoved_thenGetReturnsNull() {
        $set = new InMemoryHashSet();
        $entry = new Entry("KEY");
        $entry->setMessage("VALUE");
        $set->add($entry);
        $set->remove("KEY");
        \PHPUnit\Framework\assertNull($set->get("KEY"));
    }

    public function testWhenKeyIsNotAdded_thenGetReturnsNull() {
        $set = new InMemoryHashSet();
        \PHPUnit\Framework\assertNull($set->get("KEY"));
    }

}