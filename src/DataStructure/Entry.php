<?php

namespace Amtgard\SetQueue\DataStructure;

use Amtgard\Interface\EntryInterface;use Amtgard\Traits\Builder\Builder;use Amtgard\Traits\Builder\PostInit;use Optional\Optional;

class Entry implements EntryInterface
{
    use Builder;

    private mixed $value;
    private ?String $key;
    private bool $hasValue;

    public function __construct(string $key = null, \JsonSerializable|string $message = null)
    {
        $this->key = $key;
        $this->hasValue = false;
        Optional::ofNullable($message)
            ->ifPresent(function() use ($message) {
                $this->value = $message;
                $this->hasValue = true;
            });
    }

    public function setValue(mixed $value)
    {
        $this->hasValue = true;
        $this->value = $value;
    }

    public function getValue(): mixed
    {
        return $this->value;
    }

    public function setKey(string $key)
    {
        $this->key = $key;
    }

    public function getHash(): string
    {
        return Optional::ofNullable($this->key)->map(fn($key) => $key)->orElseGet(fn() => md5(json_encode($this->value)));
    }

    public function hasValue(): bool
    {
        return $this->hasValue;
    }

    #[PostInit]
    private function postInit(): void
    {
        if (!isset($this->value)) {
            throw new \InvalidArgumentException("In class Entry the field value must be set.");
        }
    }

    public function jsonSerialize(): mixed
    {
        return [
            'key' => $this->key,
            'value' => $this->value
        ];
    }
}