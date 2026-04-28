<?php

namespace Modules\Authorization\Infrastructure\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Authorization\Infrastructure\Models\PermissionModel;
use Modules\Authorization\Infrastructure\Models\RoleModel;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
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

        // ADMIN — unrestricted: all fields, no conditions
        $admin = RoleModel::firstOrCreate(['name' => 'admin'], ['display_name' => 'Administrator']);
        $admin->permissions()->sync(
            $permissions->mapWithKeys(fn($p) => [$p->id => ['conditions' => null]])->toArray()
        );

        // EDITOR — can view all fields but can only write limited fields
        // can only edit/delete posts they OWN
        $editor = RoleModel::firstOrCreate(['name' => 'editor'], ['display_name' => 'Editor']);
        $editor->permissions()->sync([

            // View: can read most fields, but NOT internal_notes or cost
            $byName['posts:view']->id => [
                'conditions' => json_encode([
                    'readable_fields' => ['id', 'title', 'body', 'status', 'published_at', 'user_id'],
                ]),
            ],

            // Create: can only fill in title and body (not status, published_at, cost)
            $byName['posts:create']->id => [
                'conditions' => json_encode([
                    'writable_fields' => ['title', 'body'],
                ]),
            ],

            // Edit: owner only, can only write title and body
            $byName['posts:edit']->id => [
                'conditions' => json_encode([
                    'owner_only'      => true,
                    'readable_fields' => ['id', 'title', 'body', 'status', 'published_at', 'user_id'],
                    'writable_fields' => ['title', 'body'],
                ]),
            ],

            // Delete: owner only, no field restrictions needed
            $byName['posts:delete']->id => [
                'conditions' => json_encode([
                    'owner_only' => true,
                ]),
            ],
        ]);

        // VIEWER — read-only, only published posts, minimal fields
        $viewer = RoleModel::firstOrCreate(['name' => 'viewer'], ['display_name' => 'Viewer']);
        $viewer->permissions()->sync([
            $byName['posts:view']->id => [
                'conditions' => json_encode([
                    'allowed_statuses' => ['published'],
                    'readable_fields'  => ['id', 'title', 'body', 'published_at'],
                    // no writable_fields → viewer cannot write at all
                ]),
            ],
        ]);
    }
}
