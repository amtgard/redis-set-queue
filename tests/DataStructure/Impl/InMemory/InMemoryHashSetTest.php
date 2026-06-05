<?php

declare(strict_types=1);

use Amtgard\SetQueue\DataStructure\Entry;
use Amtgard\SetQueue\DataStructure\Impl\InMemory\InMemoryHashSet;
use PHPUnit\Framework\TestCase;

class InMemoryHashSetTest extends TestCase {
    public function testWhenAddKey_ContainsKey() {
        $set = new InMemoryHashSet();
        $entry = new Entry("KEY");
        $entry->setValue("VALUE");
        $set->add($entry);
        \PHPUnit\Framework\assertTrue($set->contains($entry));
    }

    public function testWhenValueRemoved_thenNotContained() {
        $entry1 = new Entry("KEY1");
        $entry1->setValue("VALUE1");
        $entry2 = new Entry("KEY2");
        $entry2->setValue("VALUE2");
        $set = new InMemoryHashSet();
        $set->add($entry1);
        $set->add($entry2);
        \PHPUnit\Framework\assertEquals("VALUE1", $set->remove($entry1));
        \PHPUnit\Framework\assertFalse($set->contains($entry1));
    }

    public function testWhenKeyNotAdded_thenNotContained() {
        $set = new InMemoryHashSet();
        \PHPUnit\Framework\assertFalse($set->contains(Entry::builder()->key("KEY")->value("V")->build()));
    }

    public function testWhenKeyIsAdded_thenGetReturnsValue() {
        $set = new InMemoryHashSet();
        $entry = new Entry("KEY");
        $entry->setValue("VALUE");
        $set->add($entry);
        \PHPUnit\Framework\assertEquals("VALUE", $set->get($entry));
    }

    public function testWhenKeyIsRemoved_thenGetReturnsNull() {
        $set = new InMemoryHashSet();
        $entry = new Entry("KEY");
        $entry->setValue("VALUE");
        $set->add($entry);
        $set->remove($entry);
        \PHPUnit\Framework\assertNull($set->get($entry));
    }

    public function testWhenKeyIsNotAdded_thenGetReturnsNull() {
        $set = new InMemoryHashSet();
        \PHPUnit\Framework\assertNull($set->get(Entry::builder()->key("KEY")->value("V")->build()));
    }

    public function testWhenAddDuplicate_thenReturnsExistingValue() {
        $set = new InMemoryHashSet();
        $entry = Entry::builder()->key("KEY")->value("VALUE")->build();
        $set->add($entry);
        $entry2 = Entry::builder()->key("KEY")->value("OTHER")->build();
        \PHPUnit\Framework\assertEquals("VALUE", $set->add($entry2));
    }

    public function testWhenRemoveMissing_thenReturnsNull() {
        $set = new InMemoryHashSet();
        $entry = Entry::builder()->key("KEY")->value("VALUE")->build();
        \PHPUnit\Framework\assertNull($set->remove($entry));
    }

    public function testGetList() {
        $set = new InMemoryHashSet();
        $entry1 = Entry::builder()->key("KEY1")->value("VALUE1")->build();
        $entry2 = Entry::builder()->key("KEY2")->value("VALUE2")->build();
        $set->add($entry1);
        \PHPUnit\Framework\assertEquals(["VALUE1", null], $set->getList([$entry1, $entry2]));
    }

}