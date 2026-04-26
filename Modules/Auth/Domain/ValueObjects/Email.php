<?php

namespace Modules\Auth\Domain\ValueObjects;

use Modules\Auth\Domain\Exceptions\InvalidEmailException;

final class Email
{
    public function __construct(private readonly string $value) {
        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidEmailException();
        }
    }

    public function value(): string
    {
        return $this->value;
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value();
    }
}
