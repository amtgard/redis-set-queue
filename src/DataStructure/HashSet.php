<?php

namespace Amtgard\SetQueue;

interface HashSet
{
    public function add(mixed $key, mixed $value): mixed;

    public function contains(mixed $key): bool;

    public function remove(mixed $key): mixed;

    public function get(mixed $key): mixed;
}