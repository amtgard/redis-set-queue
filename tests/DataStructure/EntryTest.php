<?php

namespace DataStructure;

use Amtgard\SetQueue\DataStructure\Entry;
use PHPUnit\Framework\TestCase;

class EntryTest extends TestCase
{
    public function testBuilderCreatesEntryWithKeyAndValue(): void
    {
        $entry = Entry::builder()->key('KEY')->value('VALUE')->build();
        self::assertEquals('KEY', $entry->getHash());
        self::assertEquals('VALUE', $entry->getValue());
    }

    public function testGetHashUsesMd5WhenKeyMissing(): void
    {
        $entry = Entry::builder()->value('VALUE')->build();
        self::assertEquals(md5(json_encode('VALUE')), $entry->getHash());
    }

    public function testSetValueAndSetKey(): void
    {
        $entry = new Entry();
        $entry->setKey('KEY');
        $entry->setValue('VALUE');
        self::assertEquals('KEY', $entry->getHash());
        self::assertEquals('VALUE', $entry->getValue());
        self::assertTrue($entry->hasValue());
    }

    public function testConstructorWithMessageSetsValue(): void
    {
        $entry = new Entry('KEY', 'VALUE');
        self::assertEquals('VALUE', $entry->getValue());
        self::assertTrue($entry->hasValue());
    }

    public function testJsonSerialize(): void
    {
        $entry = Entry::builder()->key('KEY')->value('VALUE')->build();
        self::assertEquals(['key' => 'KEY', 'value' => 'VALUE'], $entry->jsonSerialize());
    }

    public function testBuilderWithoutValueThrows(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        Entry::builder()->key('KEY')->build();
    }
}
