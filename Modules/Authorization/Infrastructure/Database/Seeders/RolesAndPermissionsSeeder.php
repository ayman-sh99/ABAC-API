<?php

namespace Modules\Authorization\Infrastructure\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Authorization\Infrastructure\Models\PermissionModel;
use Modules\Authorization\Infrastructure\Models\RoleModel;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // --- Permissions ---
        $permissionsData = [
            ['name' => 'posts:view',   'group' => 'posts'],
            ['name' => 'posts:create', 'group' => 'posts'],
            ['name' => 'posts:edit',   'group' => 'posts'],
            ['name' => 'posts:delete', 'group' => 'posts'],
            ['name' => 'users:view',   'group' => 'users'],
            ['name' => 'users:manage', 'group' => 'users'],
        ];

        $permissions = collect($permissionsData)
            ->map(fn($p) => PermissionModel::firstOrCreate(['name' => $p['name']], $p));

        $byName = $permissions->keyBy('name');

        // --- Admin role: all permissions, no conditions ---
        $admin = RoleModel::firstOrCreate(['name' => 'admin'], ['display_name' => 'Administrator']);
        $admin->permissions()->sync(
            $permissions->mapWithKeys(fn($p) => [$p->id => ['conditions' => null]])->toArray()
        );

        // --- Editor role: can view all posts, but can only edit/delete their OWN posts ---
        $editor = RoleModel::firstOrCreate(['name' => 'editor'], ['display_name' => 'Editor']);
        $editor->permissions()->sync([
            $byName['posts:view']->id   => ['conditions' => null],
            $byName['posts:create']->id => ['conditions' => null],
            $byName['posts:edit']->id   => ['conditions' => json_encode(['owner_only' => true])],
            $byName['posts:delete']->id => ['conditions' => json_encode(['owner_only' => true])],
        ]);

        // --- Viewer role: read-only, only published posts ---
        $viewer = RoleModel::firstOrCreate(['name' => 'viewer'], ['display_name' => 'Viewer']);
        $viewer->permissions()->sync([
            $byName['posts:view']->id => [
                'conditions' => json_encode(['allowed_statuses' => ['published']]),
            ],
        ]);
    }
}
