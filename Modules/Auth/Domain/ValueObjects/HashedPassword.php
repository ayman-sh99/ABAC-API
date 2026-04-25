<?php

namespace Modules\Auth\Domain\ValueObjects;

final class HashedPassword
{
    public function __construct(private readonly string $hash) {}

    public function verify(string $plain_password): bool
    {
        return password_verify($plain_password, $this->hash);
    }

    public function hash(): string
    {
        return $this->hash;
    }

    public static function fromPlain(string $plain_password): self
    {
        return new self(password_hash($plain_password, PASSWORD_DEFAULT));
    }
}
