<?php

namespace Modules\Authorization\Domain\Contracts;

use Modules\Authorization\Domain\Entities\Role;
use Modules\Shared\Domain\ValueObjects\UserId;

interface RoleRepositoryContract
{
    public function findByUserId(UserId $userId): ?Role;
}
