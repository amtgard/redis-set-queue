<?php

namespace Amtgard\SetQueue\DataStructure;

interface Queue
{
    public function enqueue(String $entry);

    public function dequeue(): String;

    public function dlqDequeue(): String;

    public function dlqCommit(String $entry);

    public function commit(String $entry);

}