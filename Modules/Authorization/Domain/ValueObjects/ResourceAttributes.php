<?php

namespace Modules\Authorization\Domain\ValueObjects;

final class ResourceAttributes
{
    public function __construct (private readonly array $attributes = []) {}

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->attributes[$key] ?? $default;
    }

    public function has(string $key): bool
    {
        return array_key_exists($key, $this->attributes);
    }

    public function toArray(): array
    {
        return $this->attributes;
    }

    public static function empty(): self
    {
        return new self([]);
    }

    /**
     * Build from any Eloquent model or array.
     * ResourceAttributes::from(['owner_id' => $post->user_id, 'status' => $post->status])
     */
    public static function from(array $attributes): self
    {
        return new self($attributes);
    }
}
