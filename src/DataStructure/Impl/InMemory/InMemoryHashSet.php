<?php

namespace Amtgard\SetQueue\DataStructure\Impl\InMemory;

use Amtgard\Interface\EntryInterface;
use Amtgard\Interface\HashSetInterface;

class InMemoryHashSet implements HashSetInterface
{
    private array $set = [];

    public function add(EntryInterface $entry): mixed
    {
        if (!$this->contains($entry)) {
            $this->set[$entry->getHash()] = $entry->getValue();
        }
        return $this->set[$entry->getHash()];
    }

    public function contains(EntryInterface $entry): bool
    {
        return array_key_exists($entry->getHash(), $this->set);
    }

    public function remove(EntryInterface $entry): mixed
    {
        if ($this->contains($entry)) {
            $value = $this->set[$entry->getHash()];
            unset($this->set[$entry->getHash()]);
            return $value;
        }
        return null;
    }

    public function get(EntryInterface $entry): mixed
    {
        return $this->contains($entry) ? $this->set[$entry->getHash()] : null;
    }

    public function getList(array $entries): array
    {
        $values = [];
        foreach ($entries as $entry) {
            $values[] = $this->get($entry);
        }
        return $values;
    }
}