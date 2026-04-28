<?php

namespace Modules\Authorization\Domain\ValueObjects;

final class PolicyConditions
{
    /**
     * @param array<string, mixed> $conditions  e.g. ['owner_only' => true, 'max_amount' => 1000]
     */
    public function __construct(private readonly array $conditions = []) {}

    public function has(string $key): bool
    {
        return array_key_exists($key, $this->conditions);
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->conditions[$key] ?? $default;
    }

    public function isEmpty(): bool
    {
        return empty($this->conditions);
    }

    public function toArray(): array
    {
        return $this->conditions;
    }

    public static function empty(): self
    {
        return new self([]);
    }

    public static function fromJson(string|null $json): self
    {
        if (empty($json)) {
            return self::empty();
        }

        return new self(json_decode($json, true) ?? []);
    }
}
