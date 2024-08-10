<?php

namespace Amtgard\SetQueue\DataStructure;

interface HashSet
{
    public function add(Entry $entry): mixed;

    public function contains(mixed $key): bool;

    public function remove(mixed $key): mixed;

    public function get(mixed $key): mixed;

    public function getList(array $keys): array;
}