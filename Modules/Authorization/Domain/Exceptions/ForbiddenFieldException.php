<?php

namespace Modules\Authorization\Domain\Exceptions;

use RuntimeException;

final class ForbiddenFieldException extends RuntimeException
{
    /** @param string[] $fields */
    public function __construct(private readonly array $fields)
    {
        parent::__construct(
            "You are not allowed to write to the following fields: " . implode(', ', $fields)
        );
    }

    /** @return string[] */
    public function forbiddenFields(): array
    {
        return $this->fields;
    }
}
