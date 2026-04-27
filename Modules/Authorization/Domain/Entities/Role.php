<?php

namespace Modules\Authorization\Domain\Entities;

use Modules\Authorization\Domain\ValueObjects\RoleId;

final class Role
{
    /**
     * @param Permission[] $permissions
     */
    public function __construct(
        private readonly RoleId $id,
        private string $name,
        private array $permissions,
    ) {}


    public function hasPermission(string $permission): bool
    {
        return in_array($permission, $this->permissions);
    }

    /**
     * Retrieve the full Permission entity (including conditions).
     * Used by the PolicyEngine for ABAC evaluation.
     */
    public function findPermission(string $permissionName): ?Permission
    {
        foreach ($this->permissions as $permission) {
            if ($permission->name() === $permissionName) {
                return $permission;
            }
        }
        return null;
    }

    // Getters
    public function id(): RoleId { return $this->id; }
    public function name(): string { return $this->name; }

    /**
     * @return Permission[]
     */
    public function permissions(): array { return $this->permissions; }

}
