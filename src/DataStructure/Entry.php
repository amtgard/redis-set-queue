<?php

namespace Amtgard\SetQueue\DataStructure;

use Optional\Optional;

class Entry
{
    private mixed $message;
    private String $key;
    private bool $hasMessage;

    public function __construct(mixed $key, mixed $message = null)
    {
        $this->key = $key;
        $this->hasMessage = false;
        Optional::ofNullable($message)
            ->ifPresent(function() use ($message) {
                $this->message = $message;
                $this->hasMessage = true;
            });
    }

    public function setMessage(mixed $message)
    {
        $this->hasMessage = true;
        $this->message = $message;
    }

    public function getMessage(): mixed
    {
        return $this->message;
    }

    public function setKey(string $key)
    {
        $this->key = $key;
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function hasMessage(): bool
    {
        return $this->hasMessage;
    }

}