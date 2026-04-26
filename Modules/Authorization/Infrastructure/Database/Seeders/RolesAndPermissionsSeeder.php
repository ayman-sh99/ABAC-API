<?php

namespace Modules\Authorization\Infrastructure\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Authorization\Infrastructure\Models\PermissionModel;
use Modules\Authorization\Infrastructure\Models\RoleModel;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Create permissions
        $permissions = collect([
            ['name' => 'users.view',    'group' => 'users'],
            ['name' => 'users.manage',  'group' => 'users'],
        ])->map(fn($p) => PermissionModel::firstOrCreate(['name' => $p['name']], $p));

        // Create roles and attach permissions
        $admin = RoleModel::firstOrCreate(['name' => 'admin'], [
            'display_name' => 'Administrator',
        ]);
        $admin->permissions()->sync($permissions->pluck('id'));

        $editor = RoleModel::firstOrCreate(['name' => 'editor'], [
            'display_name' => 'Editor',
        ]);
        $editor->permissions()->sync(
            $permissions->whereIn('name', ['users.view'])->pluck('id')
        );
    }
}
