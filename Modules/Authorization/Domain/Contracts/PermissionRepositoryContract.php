<?php

namespace Modules\Authorization\Domain\Contracts;

use Modules\Authorization\Domain\Entities\Permission;

interface PermissionRepositoryContract
{
    /** Find a single permission by its name for a given user (via their role). */
    public function findForUser(int $userId, string $permissionName): ?Permission;
}
