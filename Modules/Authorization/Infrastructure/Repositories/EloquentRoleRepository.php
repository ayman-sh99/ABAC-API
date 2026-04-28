<?php

namespace Modules\Authorization\Infrastructure\Repositories;

use Modules\Authorization\Domain\Contracts\RoleRepositoryContract;
use Modules\Authorization\Domain\Entities\Permission;
use Modules\Authorization\Domain\Entities\Role;
use Modules\Authorization\Domain\ValueObjects\PermissionId;
use Modules\Authorization\Domain\ValueObjects\PolicyConditions;
use Modules\Authorization\Domain\ValueObjects\RoleId;
use Modules\Authorization\Infrastructure\Models\RoleModel;
use Modules\Shared\Domain\ValueObjects\UserId;

final class EloquentRoleRepository implements RoleRepositoryContract
{
    public function findByUserId(UserId $userId): ?Role
    {
        // Relations loaded here never in action !!
        $model = RoleModel::whereHas(
            'users',
            fn ($query) => $query->where('users.id', $userId->value())
        )
        ->with('permissions') // Load the permission here
        ->first();

        return $model ? $this->toDomainEntity($model) : null;
    }
    private function toDomainEntity(RoleModel $model): Role
    {
        $permissions = $model->permissions
            ->map(fn($p) => new Permission(
                new PermissionId($p->id),
                $p->name,
                $p->group,
                // ← map pivot conditions into the value object
                PolicyConditions::fromJson(
                    is_array($p->pivot->conditions)
                        ? json_encode($p->pivot->conditions)
                        : $p->pivot->conditions
                ),
            ))
            ->toArray();

        return new Role(new RoleId($model->id), $model->name, $permissions);
    }
}
