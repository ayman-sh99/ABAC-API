<?php

namespace Modules\Authorization\Infrastructure\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class RolePermissionPivot extends Pivot
{
    protected $table = 'role_permissions';

    protected $casts = [
        'conditions' => 'array',
    ];
}
