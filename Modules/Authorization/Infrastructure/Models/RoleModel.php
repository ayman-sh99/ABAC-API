<?php

namespace Modules\Authorization\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Modules\Auth\Infrastructure\Models\UserModel;

class RoleModel extends Model
{
    protected $table = 'roles';

    protected $fillable = [
        'name',
        'display_name',
    ];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(UserModel::class, 'user_roles', 'role_id', 'user_id');
    }

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(PermissionModel::class, 'role_permissions', 'role_id', 'permission_id');
    }
}
