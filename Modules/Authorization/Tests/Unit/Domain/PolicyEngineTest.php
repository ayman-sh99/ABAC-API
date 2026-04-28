<?php

namespace Modules\Authorization\Tests\Unit\Domain;

use Mockery;
use Modules\Authorization\Domain\Contracts\RoleRepositoryContract;
use Modules\Authorization\Domain\Entities\Permission;
use Modules\Authorization\Domain\Entities\Role;
use Modules\Authorization\Domain\Services\PolicyEngine;
use Modules\Authorization\Domain\ValueObjects\PermissionId;
use Modules\Authorization\Domain\ValueObjects\PolicyConditions;
use Modules\Authorization\Domain\ValueObjects\ResourceAttributes;
use Modules\Authorization\Domain\ValueObjects\RoleId;
use Modules\Shared\Domain\ValueObjects\UserId;
use PHPUnit\Framework\TestCase;

class PolicyEngineTest extends TestCase
{
    private function makePermission(string $name, array $conditions = []): Permission
    {
        return new Permission(
            new PermissionId(1),
            $name,
            'posts',
            new PolicyConditions($conditions),
        );
    }

    private function makeRole(array $permissions): Role
    {
        return new Role(new RoleId(1), 'editor', $permissions);
    }

    private function makeEngine(Role $role): PolicyEngine
    {
        $repo = Mockery::mock(RoleRepositoryContract::class);
        $repo->shouldReceive('findByUserId')->andReturn($role);
        return new PolicyEngine($repo);
    }

    protected function tearDown(): void { Mockery::close(); }

    // --- No conditions: plain RBAC ---
    public function test_allows_when_permission_exists_with_no_conditions(): void
    {
        $permission = $this->makePermission('posts:view');
        $engine     = $this->makeEngine($this->makeRole([$permission]));

        $result = $engine->evaluate(new UserId(1), $permission, ResourceAttributes::empty());

        $this->assertTrue($result);
    }

    // --- owner_only: user IS the owner ---
    public function test_allows_owner_when_owner_only_condition(): void
    {
        $permission = $this->makePermission('posts:edit', ['owner_only' => true]);
        $engine     = $this->makeEngine($this->makeRole([$permission]));

        $result = $engine->evaluate(
            new UserId(5),
            $permission,
            ResourceAttributes::from(['owner_id' => 5]),  // ← same as userId
        );

        $this->assertTrue($result);
    }

    // --- owner_only: user is NOT the owner ---
    public function test_denies_non_owner_when_owner_only_condition(): void
    {
        $permission = $this->makePermission('posts:edit', ['owner_only' => true]);
        $engine     = $this->makeEngine($this->makeRole([$permission]));

        $result = $engine->evaluate(
            new UserId(5),
            $permission,
            ResourceAttributes::from(['owner_id' => 99]),  // ← different user
        );

        $this->assertFalse($result);
    }

    // --- allowed_statuses ---
    public function test_allows_when_resource_status_is_in_allowed_statuses(): void
    {
        $permission = $this->makePermission('posts:view', ['allowed_statuses' => ['published']]);
        $engine     = $this->makeEngine($this->makeRole([$permission]));

        $result = $engine->evaluate(
            new UserId(1),
            $permission,
            ResourceAttributes::from(['status' => 'published']),
        );

        $this->assertTrue($result);
    }

    public function test_denies_when_resource_status_not_in_allowed_statuses(): void
    {
        $permission = $this->makePermission('posts:view', ['allowed_statuses' => ['published']]);
        $engine     = $this->makeEngine($this->makeRole([$permission]));

        $result = $engine->evaluate(
            new UserId(1),
            $permission,
            ResourceAttributes::from(['status' => 'draft']),
        );

        $this->assertFalse($result);
    }

    // --- No role ---
    public function test_denies_when_user_has_no_role(): void
    {
        $repo = Mockery::mock(RoleRepositoryContract::class);
        $repo->shouldReceive('findByUserId')->andReturn(null);
        $engine = new PolicyEngine($repo);

        $permission = $this->makePermission('posts:edit');

        $result = $engine->evaluate(
            new UserId(1),
            $permission,
            ResourceAttributes::empty(),
        );

        $this->assertFalse($result);
    }

    // --- Permission not in role ---
    public function test_denies_when_permission_not_assigned_to_role(): void
    {
        $otherPermission = $this->makePermission('users:manage');
        $engine          = $this->makeEngine($this->makeRole([$otherPermission]));

        $requestedPermission = $this->makePermission('posts:delete');

        $result = $engine->evaluate(
            new UserId(1),
            $requestedPermission,
            ResourceAttributes::empty(),
        );

        $this->assertFalse($result);
    }
}
