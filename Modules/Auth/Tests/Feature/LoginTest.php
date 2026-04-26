<?php

namespace Modules\Auth\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Auth\Infrastructure\Models\UserModel;
use Modules\Authorization\Infrastructure\Database\Seeders\RolesAndPermissionsSeeder;
use Modules\Authorization\Infrastructure\Models\RoleModel;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_login_and_receives_token_with_permissions(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        $role = RoleModel::where('name', 'admin')->first();
        $user = UserModel::factory()->create(['password' => bcrypt('password123')]);
        $user->roles()->attach($role);

        $response = $this->postJson('/api/auth/login', [
            'email'    => $user->email,
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'user'        => ['id', 'email', 'role'],
                'permissions' => [],
                'token'       => ['access_token', 'token_type'],
            ])
            ->assertJsonPath('user.role', 'admin')
            ->assertJsonPath('token.token_type', 'Bearer');

        $this->assertNotEmpty($response->json('token.access_token'));
        $this->assertNotEmpty($response->json('permissions'));
    }

    public function test_returns_401_for_wrong_password(): void
    {
        UserModel::factory()->create(['email' => 'test@test.com', 'password' => bcrypt('correct')]);

        $this->postJson('/api/auth/login', [
            'email'    => 'test@test.com',
            'password' => 'wrong',
        ])->assertStatus(401);
    }

    public function test_returns_403_for_inactive_user(): void
    {
        UserModel::factory()->inactive()->create([
            'email'    => 'inactive@test.com',
            'password' => bcrypt('password'),
        ]);

        $this->postJson('/api/auth/login', [
            'email'    => 'inactive@test.com',
            'password' => 'password',
        ])->assertStatus(403);
    }

    public function test_returns_422_for_missing_fields(): void
    {
        $this->postJson('/api/auth/login', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['email', 'password']);
    }
}
