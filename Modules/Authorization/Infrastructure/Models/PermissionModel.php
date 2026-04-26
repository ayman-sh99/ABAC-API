<?php

namespace Modules\Authorization\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;

class PermissionModel extends Model
{
    protected $table = 'permissions';

    protected $fillable = [
        'name',
        'group',
    ];
}
