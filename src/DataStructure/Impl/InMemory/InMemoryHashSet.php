<?php

namespace Amtgard\SetQueue\DataStructure\Impl;

use Amtgard\SetQueue\DataStructure\HashSet;

class InMemoryHashSet implements HashSet
{
    private array $set;

    public function add($key, $value): mixed
    {
        if (!$this->contains($key)) {
            $this->set[$key] = $value;
        }
    }

    public function contains($key): bool
    {
        return array_key_exists($key, $this->set);
    }

    public function remove($key): mixed
    {
        if ($this->contains($key)) {
            $message = $this->set[$key];
            unset($this->set[$key]);
            return $message;
        }
    }

    public function get($key): mixed
    {
        return $this->contains($key) ? $this->set[$key] : null;
    }
}