<?php

namespace Modules\Authorization\Application\DTOs;

final readonly class CheckPermissionOutputDTO
{
    public function __construct(
        public bool   $allowed,
        public string $reason = '',   // useful for debugging / audit logs
    ) {}
}
