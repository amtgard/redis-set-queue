<?php

namespace Amtgard\SetQueue\DataStructure\Impl\InMemory;

use Amtgard\SetQueue\DataStructure\Entry;
use Amtgard\SetQueue\DataStructure\HashSet;

class InMemoryHashSet implements HashSet
{
    private array $set = [];

    public function add(Entry $entry): mixed
    {
        if (!$this->contains($entry->getKey())) {
            $this->set[$entry->getKey()] = $entry->getMessage();
        }
        return $this->set[$entry->getKey()];
    }

    public function contains($key): bool
    {
        return array_key_exists($key, $this->set);
    }

    public function remove($key): mixed
    {
        if ($this->contains($key)) {
            $value = $this->set[$key];
            unset($this->set[$key]);
            return $value;
        }
        return null;
    }

    public function get($key): mixed
    {
        return $this->contains($key) ? $this->set[$key] : null;
    }

    public function getList(array $keys): array
    {
        $values = [];
        foreach ($keys as $key) {
            $values[] = $this->get($key);
        }
        return $values;
    }
}