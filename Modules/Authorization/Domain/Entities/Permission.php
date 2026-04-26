<?php

namespace Modules\Authorization\Domain\Entities;

use Modules\Authorization\Domain\ValueObjects\PermissionId;

final class Permission
{
    public function __construct(
        private readonly PermissionId $id,
        private readonly string $name,
        private readonly string $group,
    ) {}

    // Getters
    public function id(): PermissionId { return $this->id; }
    public function name(): string { return $this->name; }
    public function group(): string { return $this->group; }


}
