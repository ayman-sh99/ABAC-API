<?php

namespace Modules\Authorization\Domain\ValueObjects;

use InvalidArgumentException;

final class RoleId
{
    public function __construct(private readonly int $value)
    {
        if ($this->value <= 0) {
            throw new InvalidArgumentException('RoleId must be a positive integer.');
        }
    }

    public function value(): int
    {
        return $this->value;
    }
}
