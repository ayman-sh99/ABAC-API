<?php

namespace Modules\Authorization\Domain\Entities;

use Modules\Authorization\Domain\ValueObjects\PermissionId;
use Modules\Authorization\Domain\ValueObjects\PolicyConditions;

final class Permission
{
    public function __construct(
        private readonly PermissionId $id,
        private readonly string $name,       // e.g. "posts:edit"
        private readonly string $group,       // e.g. "posts:edit"
        private readonly PolicyConditions $conditions // e.g. ['owner_only' => true]
    ) {}

    // Getters
    public function id(): PermissionId { return $this->id; }
    public function name(): string { return $this->name; }
    public function group(): string { return $this->group; }
    public function conditions(): PolicyConditions { return $this->conditions; }


}
