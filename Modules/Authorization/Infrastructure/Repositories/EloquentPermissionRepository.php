<?php

namespace Modules\Authorization\Infrastructure\Repositories;


use Modules\Authorization\Domain\Contracts\PermissionRepositoryContract;
use Modules\Authorization\Domain\Entities\Permission;
use Modules\Authorization\Domain\ValueObjects\PermissionId;
use Modules\Authorization\Domain\ValueObjects\PolicyConditions;
use Modules\Authorization\Infrastructure\Models\PermissionModel;

final class EloquentPermissionRepository implements PermissionRepositoryContract
{
    public function findForUser(int $userId, string $permissionName): ?Permission
    {
        // Load permission + pivot conditions for this user via role
        $model = PermissionModel::where('permissions.name', $permissionName)
            ->join('role_permissions', 'permissions.id', '=', 'role_permissions.permission_id')
            ->join('roles', 'role_permissions.role_id', '=', 'roles.id')
            ->join('user_roles', 'roles.id', '=', 'user_roles.role_id')
            ->where('user_roles.user_id', $userId)
            ->select('permissions.*', 'role_permissions.conditions')
            ->first();

        if (! $model) {
            return null;
        }

        return new Permission(
            new PermissionId($model->id),
            $model->name,
            $model->group,
            PolicyConditions::fromJson($model->conditions),
        );
    }
}
