<?php

namespace Modules\Authorization\Domain\ValueObjects;

use InvalidArgumentException;

final class PermissionId
{
    public function __construct(private readonly int $value)
    {
        if ($this->value <= 0) {
            throw new InvalidArgumentException('PermissionId must be a positive integer.');
        }
    }

    public function value(): int
    {
        return $this->value;
    }
}
