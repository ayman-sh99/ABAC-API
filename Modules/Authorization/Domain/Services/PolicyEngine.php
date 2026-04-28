<?php

namespace Modules\Authorization\Domain\Services;

use Modules\Authorization\Domain\Contracts\PolicyEngineContract;
use Modules\Authorization\Domain\Contracts\RoleRepositoryContract;
use Modules\Authorization\Domain\Entities\Permission;
use Modules\Authorization\Domain\ValueObjects\FieldPermissions;
use Modules\Authorization\Domain\ValueObjects\ResourceAttributes;
use Modules\Shared\Domain\ValueObjects\UserId;

final class PolicyEngine implements PolicyEngineContract
{
    public function __construct(
        private readonly RoleRepositoryContract $roleRepository
    ) {}

    public function evaluate(UserId $userId, Permission $permission, ResourceAttributes $resource): bool
    {
        // 1. Load the user's role
        $role = $this->roleRepository->findByUserId($userId);

        if (!$role) {
            return false; // No role -> deny everything
        }

        // 2. Does the role have this permission at all?
        $rolePermission = $role->findPermission($permission->name());

        if ($rolePermission == null) {
            return false; // Role doesn't have this permission -> deny
        }

        // 3. Evaluate conditions (ABAC part)
        $conditions = $rolePermission->conditions();

        if ($conditions->isEmpty()) {
            return true; // No conditions -> plain RBAC allow
        }

        // --- Condition: owner_only ---
        // The user can only act on resources they own.
        if ($conditions->get('owner_only') === true) {
            $ownerId = $resource->get('owner_id');
            if ($ownerId === null || (int) $ownerId !== $userId->value()) {
                return false;
            }
        }

        // --- Condition: allowed_statuses ---
        // The resource must be in one of the allowed statuses.
        if ($conditions->has('allowed_statuses')) {
            $allowedStatuses = (array) $conditions->get('allowed_statuses');
            $resourceStatus  = $resource->get('status');
            if (! in_array($resourceStatus, $allowedStatuses, true)) {
                return false;
            }
        }

        // --- Condition: max_amount ---
        // Numeric ceiling check (e.g. for financial resources).
        if ($conditions->has('max_amount')) {
            $max    = (float) $conditions->get('max_amount');
            $amount = (float) $resource->get('amount', 0);
            if ($amount > $max) {
                return false;
            }
        }

        // Add more condition evaluators here as your system grows.
        // Each condition must be self-contained and easy to unit-test.

        return true;
    }

    public function resolveFieldPermissions(UserId $userId, string $permissionName): FieldPermissions
    {
        $role = $this->roleRepository->findByUserId($userId);

        if (! $role) {
            return FieldPermissions::none();
        }

        $permission = $role->findPermission($permissionName);

        if (! $permission) {
            return FieldPermissions::none();
        }

        return FieldPermissions::fromConditions($permission->conditions());
    }
}
